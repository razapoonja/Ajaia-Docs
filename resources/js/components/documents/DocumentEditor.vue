<script setup lang="ts">
import { Bold, Eye, Italic, Link, Strikethrough, Underline } from '@lucide/vue';
import { TaskItem, TaskList } from '@tiptap/extension-list';
import { CharacterCount, Placeholder } from '@tiptap/extensions';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { BubbleMenu } from '@tiptap/vue-3/menus';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import EditorToolbar from '@/components/documents/EditorToolbar.vue';
import LinkDialog from '@/components/documents/LinkDialog.vue';

const props = defineProps<{
    modelValue: string | null;
    editable: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', html: string): void;
}>();

const linkDialogOpen = ref(false);
const linkInitialUrl = ref('');
const counts = ref({ words: 0, characters: 0 });

const editor = useEditor({
    content: props.modelValue ?? '',
    editable: props.editable,
    extensions: [
        StarterKit.configure({
            heading: { levels: [1, 2, 3] },
            link: {
                openOnClick: false,
                autolink: true,
                defaultProtocol: 'https',
                protocols: ['http', 'https', 'mailto'],
                HTMLAttributes: { rel: 'noopener noreferrer nofollow', target: '_blank' },
            },
        }),
        TaskList,
        TaskItem.configure({ nested: true }),
        Placeholder.configure({ placeholder: 'Start writing, or type # for a heading, - for a list, [ ] for a checklist…' }),
        CharacterCount,
    ],
    editorProps: {
        attributes: {
            class: 'editor-prose min-h-[65vh] px-6 py-10 outline-none sm:px-12',
            'aria-label': 'Document body',
        },
        handleKeyDown: (_view, event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                openLinkDialog();

                return true;
            }

            return false;
        },
    },
    onCreate: ({ editor }) => updateCounts(editor.storage.characterCount),
    onUpdate: ({ editor }) => {
        updateCounts(editor.storage.characterCount);
        emit('update:modelValue', editor.getHTML());
    },
});

function updateCounts(storage: { words: () => number; characters: () => number }): void {
    counts.value = { words: storage.words(), characters: storage.characters() };
}

const minutes = computed(() => Math.max(1, Math.round(counts.value.words / 200)));

function openLinkDialog(): void {
    if (!editor.value || !props.editable) {
        return;
    }

    linkInitialUrl.value = editor.value.getAttributes('link').href ?? '';
    linkDialogOpen.value = true;
}

function saveLink(url: string): void {
    const chain = editor.value?.chain().focus().extendMarkRange('link');

    if (editor.value?.state.selection.empty && !editor.value.isActive('link')) {
        chain?.insertContent({ type: 'text', text: url, marks: [{ type: 'link', attrs: { href: url } }] }).run();

        return;
    }

    chain?.setLink({ href: url }).run();
}

function removeLink(): void {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
}

watch(
    () => props.editable,
    (editable) => editor.value?.setEditable(editable),
);

onBeforeUnmount(() => editor.value?.destroy());
</script>

<template>
    <div class="overflow-hidden rounded-xl border bg-card shadow-xs">
        <EditorToolbar v-if="editor && editable" :editor="editor" @link="openLinkDialog" />
        <div
            v-else-if="!editable"
            class="flex items-center gap-2 border-b bg-muted/40 px-4 py-2 text-sm text-muted-foreground"
        >
            <Eye class="size-4" aria-hidden="true" />
            You have view-only access to this document.
        </div>

        <BubbleMenu
            v-if="editor && editable"
            :editor="editor"
            class="flex items-center gap-0.5 rounded-lg border bg-popover p-1 text-popover-foreground shadow-lg"
        >
            <button
                v-for="item in [
                    { label: 'Bold', icon: Bold, mark: 'bold', run: () => editor?.chain().focus().toggleBold().run() },
                    { label: 'Italic', icon: Italic, mark: 'italic', run: () => editor?.chain().focus().toggleItalic().run() },
                    { label: 'Underline', icon: Underline, mark: 'underline', run: () => editor?.chain().focus().toggleUnderline().run() },
                    { label: 'Strikethrough', icon: Strikethrough, mark: 'strike', run: () => editor?.chain().focus().toggleStrike().run() },
                    { label: 'Link', icon: Link, mark: 'link', run: openLinkDialog },
                ]"
                :key="item.label"
                type="button"
                :aria-label="item.label"
                :aria-pressed="editor.isActive(item.mark)"
                :title="item.label"
                class="flex size-8 cursor-pointer items-center justify-center rounded-md transition-colors hover:bg-accent"
                :class="editor.isActive(item.mark) ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'"
                @click="item.run"
            >
                <component :is="item.icon" class="size-4" />
            </button>
        </BubbleMenu>

        <EditorContent :editor="editor" />

        <footer class="flex flex-wrap items-center justify-between gap-2 border-t bg-muted/30 px-4 py-2 text-xs text-muted-foreground">
            <span class="tabular-nums">
                {{ counts.words.toLocaleString() }} {{ counts.words === 1 ? 'word' : 'words' }},
                {{ counts.characters.toLocaleString() }} characters, about {{ minutes }} min to read
            </span>
            <span v-if="editable" class="hidden items-center gap-1 sm:inline-flex">
                <kbd class="rounded border bg-background px-1.5 py-0.5 font-mono text-[0.65rem]">Ctrl</kbd>
                <kbd class="rounded border bg-background px-1.5 py-0.5 font-mono text-[0.65rem]">S</kbd>
                saves now
            </span>
        </footer>

        <LinkDialog v-model:open="linkDialogOpen" :initial-url="linkInitialUrl" @save="saveLink" @remove="removeLink" />
    </div>
</template>
