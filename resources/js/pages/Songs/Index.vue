<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Check, Disc3, Plus } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import LibraryEntryController from '@/actions/App/Http/Controllers/LibraryEntryController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { index as songsIndexRoute } from '@/routes/songs';

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
    created_at: string | null;
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
    filters: { q: string };
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Shared library',
                href: songsIndexRoute(),
            },
        ],
    },
});

const q = ref<string>(props.filters.q ?? '');

const submitSearch = useDebounceFn((value: string): void => {
    router.get(
        songsIndexRoute().url,
        { q: value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}, 300);

watch(q, (value) => submitSearch(value));

const formatDuration = (seconds: number | null): string => {
    if (seconds === null || seconds <= 0) return '—';
    const m = Math.floor(seconds / 60);
    const s = (seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
};
</script>

<template>
    <Head title="Shared library" />

    <h1 class="sr-only">Shared library</h1>

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            title="Shared library"
            description="Every song uploaded to Sharewire. Add anything you like to your personal library."
        />

        <div class="flex items-center gap-3">
            <Input
                v-model="q"
                type="search"
                placeholder="Search by title, artist, album, or genre..."
                autocomplete="off"
                class="max-w-md"
                data-test="shared-library-search"
            />
            <span class="text-sm text-muted-foreground">
                {{ props.songs.meta.total }} {{ props.songs.meta.total === 1 ? 'song' : 'songs' }}
            </span>
        </div>

        <div
            v-if="props.songs.data.length === 0"
            class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed py-16 text-center"
        >
            <Disc3 class="size-10 text-muted-foreground" />
            <p class="text-sm text-muted-foreground">
                {{ props.filters.q ? 'No songs match your search.' : 'No songs have been uploaded yet.' }}
            </p>
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
                        <th class="px-4 py-3 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="song in props.songs.data"
                        :key="song.id"
                        class="border-t hover:bg-muted/30"
                        :data-test="`song-row-${song.id}`"
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
                        <td class="px-4 py-3 text-muted-foreground">{{ song.uploader.name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <Button
                                v-if="song.is_in_my_library"
                                variant="outline"
                                size="sm"
                                disabled
                            >
                                <Check class="size-4" />
                                In library
                            </Button>
                            <Form
                                v-else
                                v-bind="LibraryEntryController.store.form({ song: song.id })"
                                :options="{ preserveScroll: true }"
                                #default="{ processing }"
                            >
                                <Button
                                    type="submit"
                                    size="sm"
                                    variant="default"
                                    :disabled="processing"
                                    :data-test="`add-song-${song.id}`"
                                >
                                    <Plus class="size-4" />
                                    Add
                                </Button>
                            </Form>
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
