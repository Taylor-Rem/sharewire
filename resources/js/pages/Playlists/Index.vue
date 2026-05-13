<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import { index as playlistsIndexRoute, show as playlistShowRoute } from '@/routes/playlists';
import type { BreadcrumbItem } from '@/types';

interface Playlist {
    id: number;
    name: string;
    is_primary: boolean;
}

defineProps<{ playlists: Playlist[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Playlists', href: playlistsIndexRoute() },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Playlists" />
        <div class="p-6">
            <Heading title="Playlists" description="Your playlists." />

            <ul v-if="playlists.length" class="mt-6 space-y-2">
                <li v-for="playlist in playlists" :key="playlist.id">
                    <Link :href="playlistShowRoute(playlist.id).url" class="hover:underline">
                        {{ playlist.name }}
                        <span v-if="playlist.is_primary" class="text-xs text-muted-foreground">(primary)</span>
                    </Link>
                </li>
            </ul>
            <p v-else class="mt-6 text-sm text-muted-foreground">You don't have any playlists yet.</p>
        </div>
    </AppLayout>
</template>
