<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import SongController from '@/actions/App/Http/Controllers/SongController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create } from '@/routes/songs';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Upload song',
                href: create(),
            },
        ],
    },
});

const fileName = ref<string>('');

const onFileChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    fileName.value = input.files?.[0]?.name ?? '';
};
</script>

<template>
    <Head title="Upload song" />

    <h1 class="sr-only">Upload song</h1>

    <div class="mx-auto w-full max-w-2xl space-y-6">
        <Heading
            title="Upload a song"
            description="Add an MP3 to the shared library. You'll be able to add it to your personal library afterward."
        />

        <Form
            v-bind="SongController.store.form()"
            enctype="multipart/form-data"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <fieldset :disabled="processing" class="space-y-6 disabled:opacity-60">
                <div class="grid gap-2">
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        name="title"
                        type="text"
                        required
                        autocomplete="off"
                        placeholder="Song title"
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="artist">Artist</Label>
                    <Input
                        id="artist"
                        name="artist"
                        type="text"
                        required
                        autocomplete="off"
                        placeholder="Artist name"
                    />
                    <InputError :message="errors.artist" />
                </div>

                <div class="grid gap-2">
                    <Label for="album">Album <span class="text-muted-foreground">(optional)</span></Label>
                    <Input
                        id="album"
                        name="album"
                        type="text"
                        autocomplete="off"
                        placeholder="Album name"
                    />
                    <InputError :message="errors.album" />
                </div>

                <div class="grid gap-2">
                    <Label for="genre">Genre <span class="text-muted-foreground">(optional)</span></Label>
                    <Input
                        id="genre"
                        name="genre"
                        type="text"
                        autocomplete="off"
                        placeholder="Rock, Jazz, Electronic..."
                    />
                    <InputError :message="errors.genre" />
                </div>

                <div class="grid gap-2">
                    <Label for="audio">Audio file <span class="text-muted-foreground">(MP3, max 100 MB)</span></Label>
                    <Input
                        id="audio"
                        name="audio"
                        type="file"
                        accept="audio/mpeg,.mp3"
                        required
                        @change="onFileChange"
                    />
                    <p v-if="fileName" class="text-xs text-muted-foreground">
                        Selected: {{ fileName }}
                    </p>
                    <InputError :message="errors.audio" />
                </div>
            </fieldset>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="upload-song-button">
                    <Loader2 v-if="processing" class="size-4 animate-spin" />
                    {{ processing ? 'Uploading…' : 'Upload' }}
                </Button>
                <p v-if="processing" class="text-sm text-muted-foreground">
                    Don't close this tab — large files may take a moment.
                </p>
            </div>
        </Form>
    </div>
</template>
