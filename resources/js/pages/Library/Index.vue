<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Disc3, Pause, Play, Trash2 } from 'lucide-vue-next';
import LibraryEntryController from '@/actions/App/Http/Controllers/LibraryEntryController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { usePlayer } from '@/composables/usePlayer';
import { index as libraryIndexRoute } from '@/routes/library';
import { index as sharedLibraryRoute } from '@/routes/songs';

type Uploader = {
    id: number;
    name: string | null;
};

type SongRow = {
    id: number;
    title: string;
    artist: string;
    album: string | null;
    genre: string | null;
    duration_seconds: number | null;
    mime_type: string;
    uploader: Uploader;
    is_in_my_library: boolean;
    is_uploader: boolean;
    audio_url: string;
    created_at: string | null;
    pivot?: { id: number };
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginator<T> = {
    data: T[];
    links: PaginationLink[];
    meta: {
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
};

type Props = {
    songs: Paginator<SongRow>;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'My library',
                href: libraryIndexRoute(),
            },
        ],
    },
});

const player = usePlayer();

const onPlay = (song: SongRow): void => {
    player.play({
        id: song.id,
        title: song.title,
        artist: song.artist,
        audioUrl: song.audio_url,
    });
};

const formatDuration = (seconds: number | null): string => {
    if (seconds === null || seconds <= 0) return '—';
    const m = Math.floor(seconds / 60);
    const s = (seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
};
</script>

<template>
    <Head title="My library" />

    <h1 class="sr-only">My library</h1>

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            title="My library"
            description="Songs you've added. Click play to stream — playback persists as you navigate."
        />

        <div
            v-if="props.songs.data.length === 0"
            class="flex flex-col items-center justify-center gap-4 rounded-xl border border-dashed py-16 text-center"
        >
            <Disc3 class="size-10 text-muted-foreground" />
            <p class="text-sm text-muted-foreground">Your library is empty.</p>
            <Link :href="sharedLibraryRoute().url">
                <Button variant="outline">Browse the shared library</Button>
            </Link>
        </div>

        <div v-else class="overflow-x-auto rounded-xl border">
            <table class="w-full text-left text-sm">
                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Artist</th>
                        <th class="px-4 py-3 font-medium">Album</th>
                        <th class="px-4 py-3 font-medium">Genre</th>
                        <th class="px-4 py-3 font-medium">Duration</th>
                        <th class="px-4 py-3 font-medium">Uploader</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="song in props.songs.data"
                        :key="song.id"
                        class="border-t hover:bg-muted/30"
                        :data-test="`library-song-row-${song.id}`"
                    >
                        <td class="px-4 py-3 font-medium">{{ song.title }}</td>
                        <td class="px-4 py-3">{{ song.artist }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ song.album ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <Badge v-if="song.genre" variant="secondary">{{ song.genre }}</Badge>
                            <span v-else class="text-muted-foreground">—</span>
                        </td>
                        <td class="px-4 py-3 tabular-nums text-muted-foreground">
                            {{ formatDuration(song.duration_seconds) }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ song.uploader.name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="default"
                                    :data-test="`play-song-${song.id}`"
                                    @click="onPlay(song)"
                                >
                                    <component
                                        :is="player.isCurrentTrack(song.id).value && player.isPlaying.value ? Pause : Play"
                                        class="size-4"
                                    />
                                    {{ player.isCurrentTrack(song.id).value && player.isPlaying.value ? 'Pause' : 'Play' }}
                                </Button>
                                <Form
                                    v-if="song.pivot?.id"
                                    v-bind="LibraryEntryController.destroy.form({ libraryEntry: song.pivot.id })"
                                    :options="{ preserveScroll: true }"
                                    #default="{ processing }"
                                >
                                    <Button
                                        type="submit"
                                        size="sm"
                                        variant="outline"
                                        :disabled="processing"
                                        :data-test="`remove-song-${song.id}`"
                                    >
                                        <Trash2 class="size-4" />
                                        Remove
                                    </Button>
                                </Form>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav v-if="props.songs.meta.last_page > 1" class="flex items-center justify-center gap-1">
            <template v-for="link in props.songs.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    :class="[
                        'rounded-md border px-3 py-1.5 text-sm transition',
                        link.active
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-input hover:bg-muted',
                    ]"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="cursor-not-allowed rounded-md border border-input px-3 py-1.5 text-sm text-muted-foreground opacity-60"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>
