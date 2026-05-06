import { computed, ref } from 'vue';

export type PlayerTrack = {
    id: number;
    title: string;
    artist: string;
    audioUrl: string;
};

// Module-level singleton state — persists across Inertia navigations because
// the GlobalAudioPlayer lives in the layout (which Inertia preserves), and
// these refs live outside any component, so they survive page swaps too.
const _currentTrack = ref<PlayerTrack | null>(null);
const _audioElement = ref<HTMLAudioElement | null>(null);
const _isPlaying = ref<boolean>(false);

export function usePlayer() {
    const currentTrack = computed(() => _currentTrack.value);
    const isPlaying = computed(() => _isPlaying.value);

    const play = (track: PlayerTrack): void => {
        // Same track: toggle pause/resume
        if (_currentTrack.value?.id === track.id && _audioElement.value) {
            if (_isPlaying.value) {
                _audioElement.value.pause();
            } else {
                _audioElement.value.play().catch(() => {});
            }
            return;
        }

        // New track: swap source. The component's watch on currentTrack.id
        // calls .load() and .play() once the src is updated.
        _currentTrack.value = track;
    };

    const stop = (): void => {
        if (_audioElement.value) {
            _audioElement.value.pause();
            _audioElement.value.currentTime = 0;
        }
        _currentTrack.value = null;
        _isPlaying.value = false;
    };

    const setAudioRef = (el: HTMLAudioElement | null): void => {
        _audioElement.value = el;
    };

    const setPlaying = (value: boolean): void => {
        _isPlaying.value = value;
    };

    const isCurrentTrack = (id: number) =>
        computed(() => _currentTrack.value?.id === id);

    return {
        currentTrack,
        isPlaying,
        play,
        stop,
        setAudioRef,
        setPlaying,
        isCurrentTrack,
    };
}
