<script setup lang="ts">
import type { Editor } from '@tiptap/vue-3';
import {
    Bold,
    Code,
    Heading1,
    Heading2,
    Heading3,
    Italic,
    Link,
    List,
    ListOrdered,
    ListTodo,
    Minus,
    Pilcrow,
    Redo2,
    SquareCode,
    Strikethrough,
    TextQuote,
    Underline,
    Undo2,
    Unlink,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

const props = defineProps<{ editor: Editor }>();

const emit = defineEmits<{ (e: 'link'): void }>();

type Tool = {
    label: string;
    shortcut?: string;
    icon: LucideIcon;
    run: () => void;
    active?: () => boolean;
    disabled?: () => boolean;
};

const chain = () => props.editor.chain().focus();

const groups = computed<Tool[][]>(() => [
    [
        { label: 'Undo', shortcut: 'Ctrl+Z', icon: Undo2, run: () => chain().undo().run(), disabled: () => !props.editor.can().undo() },
        { label: 'Redo', shortcut: 'Ctrl+Shift+Z', icon: Redo2, run: () => chain().redo().run(), disabled: () => !props.editor.can().redo() },
    ],
    [
        { label: 'Normal text', shortcut: 'Ctrl+Alt+0', icon: Pilcrow, run: () => chain().setParagraph().run(), active: () => props.editor.isActive('paragraph') },
        { label: 'Heading 1', shortcut: 'Ctrl+Alt+1', icon: Heading1, run: () => chain().toggleHeading({ level: 1 }).run(), active: () => props.editor.isActive('heading', { level: 1 }) },
        { label: 'Heading 2', shortcut: 'Ctrl+Alt+2', icon: Heading2, run: () => chain().toggleHeading({ level: 2 }).run(), active: () => props.editor.isActive('heading', { level: 2 }) },
        { label: 'Heading 3', shortcut: 'Ctrl+Alt+3', icon: Heading3, run: () => chain().toggleHeading({ level: 3 }).run(), active: () => props.editor.isActive('heading', { level: 3 }) },
    ],
    [
        { label: 'Bold', shortcut: 'Ctrl+B', icon: Bold, run: () => chain().toggleBold().run(), active: () => props.editor.isActive('bold') },
        { label: 'Italic', shortcut: 'Ctrl+I', icon: Italic, run: () => chain().toggleItalic().run(), active: () => props.editor.isActive('italic') },
        { label: 'Underline', shortcut: 'Ctrl+U', icon: Underline, run: () => chain().toggleUnderline().run(), active: () => props.editor.isActive('underline') },
        { label: 'Strikethrough', shortcut: 'Ctrl+Shift+S', icon: Strikethrough, run: () => chain().toggleStrike().run(), active: () => props.editor.isActive('strike') },
        { label: 'Inline code', shortcut: 'Ctrl+E', icon: Code, run: () => chain().toggleCode().run(), active: () => props.editor.isActive('code') },
    ],
    [
        { label: 'Bullet list', shortcut: 'Ctrl+Shift+8', icon: List, run: () => chain().toggleBulletList().run(), active: () => props.editor.isActive('bulletList') },
        { label: 'Numbered list', shortcut: 'Ctrl+Shift+7', icon: ListOrdered, run: () => chain().toggleOrderedList().run(), active: () => props.editor.isActive('orderedList') },
        { label: 'Checklist', shortcut: 'Ctrl+Shift+9', icon: ListTodo, run: () => chain().toggleTaskList().run(), active: () => props.editor.isActive('taskList') },
    ],
    [
        { label: 'Quote', shortcut: 'Ctrl+Shift+B', icon: TextQuote, run: () => chain().toggleBlockquote().run(), active: () => props.editor.isActive('blockquote') },
        { label: 'Code block', shortcut: 'Ctrl+Alt+C', icon: SquareCode, run: () => chain().toggleCodeBlock().run(), active: () => props.editor.isActive('codeBlock') },
        { label: 'Divider', icon: Minus, run: () => chain().setHorizontalRule().run() },
    ],
    [
        { label: 'Link', shortcut: 'Ctrl+K', icon: Link, run: () => emit('link'), active: () => props.editor.isActive('link') },
        { label: 'Remove link', icon: Unlink, run: () => chain().extendMarkRange('link').unsetLink().run(), disabled: () => !props.editor.isActive('link') },
    ],
]);

function tooltip(tool: Tool): string {
    return tool.shortcut ? `${tool.label} (${tool.shortcut})` : tool.label;
}
</script>

<template>
    <div
        role="toolbar"
        aria-label="Formatting"
        class="sticky top-0 z-10 flex items-center gap-0.5 overflow-x-auto border-b bg-card/95 px-2 py-1.5 backdrop-blur [scrollbar-width:none] supports-[backdrop-filter]:bg-card/80 sm:flex-wrap sm:overflow-visible"
    >
        <template v-for="(group, index) in groups" :key="index">
            <Separator v-if="index > 0" orientation="vertical" class="mx-1 h-5! shrink-0" />
            <Button
                v-for="tool in group"
                :key="tool.label"
                type="button"
                variant="ghost"
                size="icon-sm"
                :aria-label="tool.label"
                :aria-keyshortcuts="tool.shortcut"
                :aria-pressed="tool.active ? tool.active() : undefined"
                :disabled="tool.disabled?.() ?? false"
                :class="[tool.active?.() ? 'bg-accent text-accent-foreground shadow-[inset_0_0_0_1px_var(--border)]' : 'text-muted-foreground hover:text-foreground', 'shrink-0']"
                :title="tooltip(tool)"
                @click="tool.run"
            >
                <component :is="tool.icon" />
            </Button>
        </template>
    </div>
</template>
