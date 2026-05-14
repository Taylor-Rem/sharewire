<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import PlaylistController from '@/actions/App/Http/Controllers/PlaylistController';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { index as playlistsIndexRoute, show as playlistShowRoute } from '@/routes/playlists';
import type { BreadcrumbItem } from '@/types';

type Playlist = App.Data.PlaylistData;

defineProps<{ playlists: Playlist[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Playlists', href: playlistsIndexRoute() },
];

const dialogOpen = ref(false);

const form = useForm({
    name: '',
});

const submit = (): void => {
    form.post(PlaylistController.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            dialogOpen.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Playlists" />
        <div class="p-6">
            <div class="flex items-start justify-between gap-4">
                <Heading title="Playlists" description="Your playlists." />

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button data-test="create-playlist-button">
                            <Plus class="size-4" />
                            Create playlist
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create playlist</DialogTitle>
                            <DialogDescription>Give your new playlist a name. You can add songs to it from the shared library.</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submit" class="grid gap-4">
                            <div class="grid gap-2">
                                <Label for="playlist-name">Name</Label>
                                <Input
                                    id="playlist-name"
                                    v-model="form.name"
                                    type="text"
                                    autocomplete="off"
                                    maxlength="120"
                                    placeholder="e.g. Workout, Chill, Road trip"
                                    data-test="create-playlist-name"
                                    autofocus
                                />
                                <p v-if="form.errors.name" class="text-sm text-destructive" data-test="create-playlist-error">
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            <DialogFooter>
                                <DialogClose as-child>
                                    <Button type="button" variant="secondary">Cancel</Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    data-test="create-playlist-submit"
                                >
                                    <Plus class="size-4" />
                                    Create
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <ul v-if="playlists.length" class="mt-6 space-y-2">
                <li v-for="playlist in playlists" :key="playlist.id">
                    <Link :href="playlistShowRoute(playlist.id).url" class="hover:underline">
                        {{ playlist.name }}
                        <span v-if="playlist.is_primary" class="text-xs text-muted-foreground">(primary)</span>
                        <span v-if="playlist.playlist_songs_count !== undefined" class="text-xs text-muted-foreground">
                            — {{ playlist.playlist_songs_count }} {{ playlist.playlist_songs_count === 1 ? 'song' : 'songs' }}
                        </span>
                    </Link>
                </li>
            </ul>
            <p v-else class="mt-6 text-sm text-muted-foreground">You don't have any playlists yet.</p>
        </div>
    </AppLayout>
</template>
