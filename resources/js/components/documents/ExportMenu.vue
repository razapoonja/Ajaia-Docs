<script setup lang="ts">
import { Download, FileCode, FileText, Printer } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { exportHtml, exportMarkdown, printDocument } from '@/lib/documentExport';

const props = defineProps<{
    title: string;
    content: string;
}>();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon-sm" aria-label="Export document" title="Export" class="text-muted-foreground">
                <Download />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-52">
            <DropdownMenuLabel>Export</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem class="cursor-pointer" @select="exportMarkdown(props.title, props.content)">
                <FileText /> Markdown (.md)
            </DropdownMenuItem>
            <DropdownMenuItem class="cursor-pointer" @select="exportHtml(props.title, props.content)">
                <FileCode /> Web page (.html)
            </DropdownMenuItem>
            <DropdownMenuItem class="cursor-pointer" @select="printDocument(props.title, props.content)">
                <Printer /> Print or save as PDF
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
