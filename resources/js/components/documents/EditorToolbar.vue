<script setup lang="ts">
import type { Editor } from '@tiptap/vue-3';
import {
    Bold,
    Heading1,
    Heading2,
    Heading3,
    Italic,
    List,
    ListOrdered,
    Redo2,
    Underline,
    Undo2,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

const props = defineProps<{ editor: Editor }>();

type Tool = {
    label: string;
    icon: LucideIcon;
    run: () => void;
    active?: () => boolean;
    disabled?: () => boolean;
};

const groups: Tool[][] = [
    [
        { label: 'Bold', icon: Bold, run: () => props.editor.chain().focus().toggleBold().run(), active: () => props.editor.isActive('bold') },
        { label: 'Italic', icon: Italic, run: () => props.editor.chain().focus().toggleItalic().run(), active: () => props.editor.isActive('italic') },
        { label: 'Underline', icon: Underline, run: () => props.editor.chain().focus().toggleUnderline().run(), active: () => props.editor.isActive('underline') },
    ],
    [
        { label: 'Heading 1', icon: Heading1, run: () => props.editor.chain().focus().toggleHeading({ level: 1 }).run(), active: () => props.editor.isActive('heading', { level: 1 }) },
        { label: 'Heading 2', icon: Heading2, run: () => props.editor.chain().focus().toggleHeading({ level: 2 }).run(), active: () => props.editor.isActive('heading', { level: 2 }) },
        { label: 'Heading 3', icon: Heading3, run: () => props.editor.chain().focus().toggleHeading({ level: 3 }).run(), active: () => props.editor.isActive('heading', { level: 3 }) },
    ],
    [
        { label: 'Bullet list', icon: List, run: () => props.editor.chain().focus().toggleBulletList().run(), active: () => props.editor.isActive('bulletList') },
        { label: 'Numbered list', icon: ListOrdered, run: () => props.editor.chain().focus().toggleOrderedList().run(), active: () => props.editor.isActive('orderedList') },
    ],
    [
        { label: 'Undo', icon: Undo2, run: () => props.editor.chain().focus().undo().run(), disabled: () => !props.editor.can().undo() },
        { label: 'Redo', icon: Redo2, run: () => props.editor.chain().focus().redo().run(), disabled: () => !props.editor.can().redo() },
    ],
];
</script>

<template>
    <div
        role="toolbar"
        aria-label="Formatting"
        class="flex flex-wrap items-center gap-1 border-b bg-card px-2 py-1.5"
    >
        <template v-for="(group, index) in groups" :key="index">
            <Separator v-if="index > 0" orientation="vertical" class="mx-1 h-5!" />
            <Button
                v-for="tool in group"
                :key="tool.label"
                type="button"
                variant="ghost"
                size="icon-sm"
                :aria-label="tool.label"
                :aria-pressed="tool.active?.() ?? undefined"
                :disabled="tool.disabled?.() ?? false"
                :class="tool.active?.() ? 'bg-accent text-accent-foreground' : 'text-muted-foreground'"
                :title="tool.label"
                @click="tool.run"
            >
                <component :is="tool.icon" />
            </Button>
        </template>
    </div>
</template>
