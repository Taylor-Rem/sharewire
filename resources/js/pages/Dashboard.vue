<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Headphones, Library, Upload } from 'lucide-vue-next';
import type { Component } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as myLibraryRoute } from '@/routes/playlist_song';
import { index as playlistsIndexRoute } from '@/routes/playlists';
import { create as uploadSongRoute, index as sharedLibraryRoute } from '@/routes/songs';

type DashboardCard = {
    title: string;
    description: string;
    href: { url: string };
    icon: Component;
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const cards: DashboardCard[] = [
    {
        title: 'Playlists',
        description: 'Listen to the songs you\'ve added. The player follows you across the app.',
        href: playlistsIndexRoute(),
        icon: Headphones,
    },
    {
        title: 'Shared library',
        description: 'Browse every song uploaded to Sharewire and add tracks to your personal library.',
        href: sharedLibraryRoute(),
        icon: Library,
    },
    {
        title: 'Upload song',
        description: "Add an MP3 to the shared library. Title, artist, album, and genre — that's it.",
        href: uploadSongRoute(),
        icon: Upload,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <h1 class="sr-only">Dashboard</h1>

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="card in cards"
                :key="card.title"
                :href="card.href.url"
                class="group rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                :data-test="`dashboard-card-${card.title.toLowerCase().replace(/\s+/g, '-')}`"
            >
                <Card class="h-full transition group-hover:border-primary group-hover:shadow-md">
                    <CardHeader>
                        <component :is="card.icon" class="mb-2 size-6 text-muted-foreground group-hover:text-primary" />
                        <CardTitle>{{ card.title }}</CardTitle>
                        <CardDescription>{{ card.description }}</CardDescription>
                    </CardHeader>
                    <CardContent class="flex justify-end">
                        <ArrowRight class="size-5 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-primary" />
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
