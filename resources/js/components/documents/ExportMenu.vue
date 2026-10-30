<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Copy, Download, FileCode, FileText, Printer } from '@lucide/vue';
import { duplicate } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    exportHtml,
    exportMarkdown,
    printDocument,
} from '@/lib/documentExport';

const props = defineProps<{
    documentId: number;
    title: string;
    content: string;
}>();

function makeCopy(): void {
    router.post(duplicate.url({ document: props.documentId }));
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon-sm"
                aria-label="Copy or export"
                title="Copy or export"
                class="text-muted-foreground"
            >
                <Download />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-52">
            <DropdownMenuItem class="cursor-pointer" @select="makeCopy">
                <Copy /> Make a copy
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuLabel>Export</DropdownMenuLabel>
            <DropdownMenuItem
                class="cursor-pointer"
                @select="exportMarkdown(props.title, props.content)"
            >
                <FileText /> Markdown (.md)
            </DropdownMenuItem>
            <DropdownMenuItem
                class="cursor-pointer"
                @select="exportHtml(props.title, props.content)"
            >
                <FileCode /> Web page (.html)
            </DropdownMenuItem>
            <DropdownMenuItem
                class="cursor-pointer"
                @select="printDocument(props.title, props.content)"
            >
                <Printer /> Print or save as PDF
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
