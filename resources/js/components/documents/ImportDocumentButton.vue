<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Upload } from '@lucide/vue';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { store } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import { Button } from '@/components/ui/button';
import { importDocumentFile } from '@/lib/documentImport';

const input = ref<HTMLInputElement | null>(null);
const busy = ref(false);

async function onChange(event: Event): Promise<void> {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) {
        return;
    }

    busy.value = true;

    try {
        const imported = await importDocumentFile(file);
        router.post(store.url(), imported, { onFinish: () => (busy.value = false) });
    } catch (error) {
        busy.value = false;
        toast.error(error instanceof Error ? error.message : 'That file could not be read.');
    } finally {
        if (input.value) {
            input.value.value = '';
        }
    }
}
</script>

<template>
    <div>
        <input
            ref="input"
            type="file"
            accept=".txt,.md,.docx"
            class="sr-only"
            aria-label="Import a .txt, .md or .docx file"
            @change="onChange"
        />
        <Button type="button" variant="outline" :disabled="busy" @click="input?.click()">
            <Upload />
            {{ busy ? 'Importing…' : 'Import file' }}
        </Button>
    </div>
</template>
