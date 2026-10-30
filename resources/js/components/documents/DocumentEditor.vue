<script setup lang="ts">
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, watch } from 'vue';
import EditorToolbar from '@/components/documents/EditorToolbar.vue';

const props = defineProps<{
    modelValue: string | null;
    editable: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', html: string): void;
}>();

const editor = useEditor({
    content: props.modelValue ?? '',
    editable: props.editable,
    extensions: [StarterKit.configure({ heading: { levels: [1, 2, 3] } })],
    editorProps: {
        attributes: {
            class: 'editor-prose min-h-[60vh] px-6 py-8 outline-none sm:px-10',
            'aria-label': 'Document body',
        },
    },
    onUpdate: ({ editor }) => emit('update:modelValue', editor.getHTML()),
});

watch(
    () => props.editable,
    (editable) => editor.value?.setEditable(editable),
);

onBeforeUnmount(() => editor.value?.destroy());
</script>

<template>
    <div class="overflow-hidden rounded-xl border bg-card">
        <EditorToolbar v-if="editor && editable" :editor="editor" />
        <div
            v-else-if="!editable"
            class="border-b bg-muted/40 px-4 py-2 text-sm text-muted-foreground"
        >
            You have view-only access to this document.
        </div>
        <EditorContent :editor="editor" />
    </div>
</template>
