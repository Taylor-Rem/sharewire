<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Disc3, Headphones, Library, Upload } from 'lucide-vue-next';
import type { Component } from 'vue';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

type Feature = {
    title: string;
    body: string;
    icon: Component;
};

const features: Feature[] = [
    {
        title: 'Upload',
        body: 'Drop in an MP3 with a title, artist, album, and genre. Files live on private storage and are streamed only to authenticated users.',
        icon: Upload,
    },
    {
        title: 'Browse',
        body: "Search the shared catalog by title, artist, album, or genre. Add anything that catches your ear to your personal library.",
        icon: Library,
    },
    {
        title: 'Listen',
        body: 'Play from your library in the browser. The player follows you across pages — no interruption when you navigate.',
        icon: Headphones,
    },
];
</script>

<template>
    <Head title="Sharewire — share music with people you trust" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="border-b">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-primary-foreground">
                        <Disc3 class="size-5" />
                    </div>
                    <span class="text-lg font-semibold tracking-tight">Sharewire</span>
                </div>

                <nav class="flex items-center gap-2">
                    <template v-if="$page.props.auth.user">
                        <Link :href="dashboard().url">
                            <Button>Open dashboard</Button>
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login().url">
                            <Button variant="ghost">Log in</Button>
                        </Link>
                        <Link v-if="canRegister" :href="register().url">
                            <Button>Create account</Button>
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <section class="mx-auto flex w-full max-w-4xl flex-col items-center gap-6 px-6 py-20 text-center">
                <h1 class="text-balance text-4xl font-bold tracking-tight sm:text-5xl">
                    A small music library, shared by everyone who has the link.
                </h1>
                <p class="max-w-2xl text-balance text-lg text-muted-foreground">
                    Upload an MP3 to a shared catalog. Add tracks to your personal library. Listen
                    in the browser without leaving the page you're on.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <template v-if="$page.props.auth.user">
                        <Link :href="dashboard().url">
                            <Button size="lg">Open dashboard</Button>
                        </Link>
                    </template>
                    <template v-else>
                        <Link v-if="canRegister" :href="register().url">
                            <Button size="lg" data-test="hero-register">Create an account</Button>
                        </Link>
                        <Link :href="login().url">
                            <Button variant="outline" size="lg" data-test="hero-login">Log in</Button>
                        </Link>
                    </template>
                </div>
            </section>

            <section class="mx-auto w-full max-w-6xl px-6 pb-20">
                <div class="grid gap-6 md:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="flex flex-col gap-3 rounded-xl border bg-card p-6"
                    >
                        <div class="flex aspect-square size-10 items-center justify-center rounded-md bg-muted">
                            <component :is="feature.icon" class="size-5 text-foreground" />
                        </div>
                        <h2 class="text-lg font-semibold">{{ feature.title }}</h2>
                        <p class="text-sm text-muted-foreground">{{ feature.body }}</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t">
            <div class="mx-auto flex w-full max-w-6xl flex-col items-center justify-between gap-2 px-6 py-6 text-sm text-muted-foreground sm:flex-row">
                <p>Sharewire — built with Laravel 13, Inertia.js, and Vue 3.</p>
                <p>Self-hosted on a Debian laptop, served via Tailscale Funnel.</p>
            </div>
        </footer>
    </div>
</template>
