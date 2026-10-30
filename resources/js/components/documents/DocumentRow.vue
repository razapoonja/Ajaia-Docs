<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight, FileText } from '@lucide/vue';
import { show } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import { relativeTime } from '@/lib/relativeTime';

defineProps<{
    id: number;
    title: string;
    updatedAt: string;
    meta?: string;
}>();
</script>

<template>
    <li>
        <Link
            :href="show(id)"
            class="group flex items-center gap-4 px-4 py-3.5 transition-colors hover:bg-accent/50 focus-visible:bg-accent/50 focus-visible:outline-none"
        >
            <span
                class="flex size-10 shrink-0 items-center justify-center rounded-lg border bg-background text-muted-foreground transition-colors group-hover:border-primary/30 group-hover:text-primary"
            >
                <FileText class="size-5" />
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-medium">{{ title }}</span>
                <span class="mt-0.5 block text-xs text-muted-foreground">
                    <template v-if="meta">{{ meta }} · </template>Edited
                    {{ relativeTime(updatedAt) }}
                </span>
            </span>
            <slot name="trailing" />
            <ChevronRight
                class="size-4 shrink-0 text-muted-foreground/50 transition-transform group-hover:translate-x-0.5 group-hover:text-muted-foreground"
            />
        </Link>
    </li>
</template>
