<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { usePlayer } from '@/composables/usePlayer';

const player = usePlayer();
const audioRef = ref<HTMLAudioElement | null>(null);

onMounted(() => {
    if (audioRef.value) {
        player.setAudioRef(audioRef.value);
    }
});

// When the current track id changes, reload the source and start playback.
watch(
    () => player.currentTrack.value?.id,
    async (id) => {
        if (id && audioRef.value) {
            audioRef.value.load();
            try {
                await audioRef.value.play();
            } catch {
                // Autoplay may be blocked if there was no recent user gesture;
                // the user can hit play in the controls as a fallback.
            }
        }
    },
);

const onPlay = (): void => player.setPlaying(true);
const onPause = (): void => player.setPlaying(false);
const onEnded = (): void => player.setPlaying(false);

const close = (): void => player.stop();
</script>

<template>
    <Transition name="slide-up">
        <div
            v-if="player.currentTrack.value"
            class="fixed inset-x-0 bottom-0 z-50 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80"
            data-test="global-audio-player"
        >
            <div class="mx-auto flex max-w-7xl items-center gap-4 p-3">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">
                        {{ player.currentTrack.value?.title }}
                    </p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{ player.currentTrack.value?.artist }}
                    </p>
                </div>

                <audio
                    ref="audioRef"
                    :src="player.currentTrack.value?.audioUrl"
                    controls
                    class="h-10 max-w-md flex-1"
                    preload="auto"
                    @play="onPlay"
                    @pause="onPause"
                    @ended="onEnded"
                />

                <Button
                    variant="ghost"
                    size="icon"
                    aria-label="Close player"
                    @click="close"
                >
                    <X class="size-4" />
                </Button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 200ms ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
}
</style>
