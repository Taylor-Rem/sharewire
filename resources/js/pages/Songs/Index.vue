<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Check, ChevronDown, Disc3, Plus, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import PlaylistSongController from '@/actions/App/Http/Controllers/PlaylistSongController';
import SongController from '@/actions/App/Http/Controllers/SongController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as songsIndexRoute } from '@/routes/songs';

type SongRow = App.Data.SongData;
type Playlist = App.Data.PlaylistData;

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
    filters: { q: string; mine: boolean };
    playlists: Playlist[];
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
const mineOnly = ref<boolean>(props.filters.mine ?? false);

const visit = (params: { q?: string; mine?: boolean }): void => {
    router.get(
        songsIndexRoute().url,
        {
            q: params.q || undefined,
            mine: params.mine ? 1 : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const submitSearch = useDebounceFn((value: string): void => {
    visit({ q: value, mine: mineOnly.value });
}, 300);

watch(q, (value) => submitSearch(value));
watch(mineOnly, (value) => visit({ q: q.value, mine: value }));

const addToPlaylist = (song: SongRow, playlist: Playlist): void => {
    router.post(
        PlaylistSongController.store.url({ song: song.id }),
        { playlist_id: playlist.id },
        { preserveScroll: true, preserveState: true },
    );
};

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

        <div class="flex flex-wrap items-center gap-4">
            <Input
                v-model="q"
                type="search"
                placeholder="Search by title, artist, album, or genre..."
                autocomplete="off"
                class="max-w-md"
                data-test="shared-library-search"
            />
            <div class="flex items-center gap-2">
                <Checkbox
                    id="mine-only"
                    v-model="mineOnly"
                    data-test="mine-only-toggle"
                />
                <Label for="mine-only" class="cursor-pointer">My uploads only</Label>
            </div>
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
                <template v-if="props.filters.q">No songs match your search.</template>
                <template v-else-if="props.filters.mine">You haven't uploaded any songs yet.</template>
                <template v-else>No songs have been uploaded yet.</template>
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
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
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
                            <div class="flex items-center justify-end gap-2">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            size="sm"
                                            :variant="song.my_playlist_ids.length > 0 ? 'outline' : 'default'"
                                            :data-test="`add-song-${song.id}`"
                                        >
                                            <Check v-if="song.my_playlist_ids.length > 0" class="size-4" />
                                            <Plus v-else class="size-4" />
                                            {{ song.my_playlist_ids.length > 0 ? 'In playlist' : 'Add' }}
                                            <ChevronDown class="size-4 opacity-60" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-56">
                                        <DropdownMenuLabel>Add to playlist</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            v-for="playlist in props.playlists"
                                            :key="playlist.id"
                                            @select="addToPlaylist(song, playlist)"
                                            :data-test="`add-song-${song.id}-to-playlist-${playlist.id}`"
                                        >
                                            <Check
                                                v-if="song.my_playlist_ids.includes(playlist.id)"
                                                class="size-4"
                                            />
                                            <span v-else class="size-4" />
                                            <span>{{ playlist.name }}</span>
                                            <span
                                                v-if="playlist.is_primary"
                                                class="ml-auto text-xs text-muted-foreground"
                                            >Primary</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>

                                <Dialog v-if="song.is_uploader">
                                    <DialogTrigger as-child>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            :data-test="`delete-song-${song.id}`"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>Delete "{{ song.title }}"?</DialogTitle>
                                            <DialogDescription>
                                                This permanently removes the upload from the shared library and from
                                                every other user's personal library. The audio file is deleted from
                                                disk. This cannot be undone.
                                            </DialogDescription>
                                        </DialogHeader>
                                        <DialogFooter>
                                            <DialogClose as-child>
                                                <Button variant="secondary">Cancel</Button>
                                            </DialogClose>
                                            <Form
                                                v-bind="SongController.destroy.form({ song: song.id })"
                                                :options="{ preserveScroll: true }"
                                                #default="{ processing }"
                                            >
                                                <Button
                                                    type="submit"
                                                    variant="destructive"
                                                    :disabled="processing"
                                                    :data-test="`confirm-delete-song-${song.id}`"
                                                >
                                                    <Trash2 class="size-4" />
                                                    Delete song
                                                </Button>
                                            </Form>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>
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
