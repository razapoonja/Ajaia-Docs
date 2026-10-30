<script setup lang="ts">
import { Head, router, setLayoutProps } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { AlertCircle, Check, Loader2, Pencil, Trash2 } from '@lucide/vue';
import { computed, ref, watchEffect } from 'vue';
import { destroy, index, show, update } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import DocumentEditor from '@/components/documents/DocumentEditor.vue';
import ShareDialog from '@/components/documents/ShareDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { DocumentDetail, DocumentPermissions, DocumentShare } from '@/types';

const props = defineProps<{
    document: DocumentDetail;
    permissions: DocumentPermissions;
    shares: DocumentShare[];
}>();


type SaveState = 'saved' | 'dirty' | 'saving' | 'error';

const title = ref(props.document.title);
const content = ref(props.document.content ?? '');

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Documents', href: index() },
            { title: title.value, href: show(props.document.id) },
        ],
    });
});
const saveState = ref<SaveState>('saved');
const deleting = ref(false);

const saveLabel = computed<string>(() => ({
    saved: 'Saved',
    dirty: 'Unsaved changes',
    saving: 'Saving…',
    error: 'Could not save. Check your connection and keep typing to retry.',
})[saveState.value]);

const updateUrl = update.url({ document: props.document.id });

function persist(payload: Partial<{ title: string; content: string }>): void {
    saveState.value = 'saving';
    router.patch(updateUrl, payload, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => (saveState.value = 'saved'),
        onError: () => (saveState.value = 'error'),
    });
}

const persistContent = useDebounceFn(() => persist({ content: content.value }), 800);

function onContentChange(html: string): void {
    content.value = html;
    saveState.value = 'dirty';
    void persistContent();
}

function onTitleBlur(): void {
    const next = title.value.trim();

    if (next === '') {
        title.value = props.document.title;
        return;
    }

    if (next !== props.document.title) {
        title.value = next;
        persist({ title: next });
    }
}

function deleteDocument(): void {
    deleting.value = true;
    router.delete(destroy.url({ document: props.document.id }), {
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-5 p-4 sm:p-8">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <div v-if="permissions.update" class="group relative -ml-2">
                    <input
                        v-model="title"
                        type="text"
                        maxlength="200"
                        aria-label="Document title"
                        class="w-full truncate rounded-md bg-transparent py-1 pr-8 pl-2 text-2xl font-semibold tracking-tight outline-none transition-colors hover:bg-accent/50 focus-visible:bg-accent/50 focus-visible:ring-2 focus-visible:ring-ring/40"
                        @blur="onTitleBlur"
                        @keydown.enter.prevent="($event.target as HTMLInputElement).blur()"
                    />
                    <Pencil
                        class="pointer-events-none absolute top-1/2 right-2 size-4 -translate-y-1/2 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 group-focus-within:opacity-0"
                        aria-hidden="true"
                    />
                </div>
                <h1 v-else class="truncate text-2xl font-semibold tracking-tight">{{ title }}</h1>

                <p class="mt-1.5 flex flex-wrap items-center gap-x-2 text-xs text-muted-foreground">
                    <span>Owned by {{ document.owner.name }}</span>
                    <span aria-hidden="true">·</span>
                    <span
                        aria-live="polite"
                        class="inline-flex items-center gap-1"
                        :class="saveState === 'error' ? 'text-destructive' : ''"
                    >
                        <Loader2 v-if="saveState === 'saving'" class="size-3 animate-spin" aria-hidden="true" />
                        <AlertCircle v-else-if="saveState === 'error'" class="size-3" aria-hidden="true" />
                        <Check v-else-if="saveState === 'saved'" class="size-3" aria-hidden="true" />
                        {{ saveLabel }}
                    </span>
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <Badge v-if="!permissions.update" variant="outline">Can view</Badge>
                <Badge v-else-if="!permissions.share" variant="outline">Can edit</Badge>
                <ShareDialog v-if="permissions.share" :document-id="document.id" :shares="shares" />

                <Dialog v-if="permissions.delete">
                    <DialogTrigger as-child>
                        <Button variant="ghost" size="icon-sm" aria-label="Delete document" class="text-muted-foreground hover:text-destructive">
                            <Trash2 />
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Delete "{{ title }}"?</DialogTitle>
                            <DialogDescription>
                                This removes the document for you and everyone it is shared with. There is no undo.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button variant="destructive" :disabled="deleting" @click="deleteDocument">
                                {{ deleting ? 'Deleting…' : 'Delete document' }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </header>

        <DocumentEditor
            :model-value="content"
            :editable="permissions.update"
            @update:model-value="onContentChange"
        />
    </div>
</template>
