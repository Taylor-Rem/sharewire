<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import GlobalAudioPlayer from '@/components/GlobalAudioPlayer.vue';
import { Toaster } from '@/components/ui/sonner';
import { usePlayer } from '@/composables/usePlayer';
import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const player = usePlayer();

// Reserve space at the bottom while the player is visible so it never
// overlaps page content.
const contentPaddingClass = computed(() =>
    player.currentTrack.value ? 'pb-24' : '',
);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent
            variant="sidebar"
            :class="['overflow-x-hidden', contentPaddingClass]"
        >
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <Toaster />
        <GlobalAudioPlayer />
    </AppShell>
</template>
