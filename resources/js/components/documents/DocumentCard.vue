<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import { relativeTime } from '@/lib/relativeTime';

defineProps<{
    id: number;
    title: string;
    excerpt: string;
    updatedAt: string;
    meta?: string;
}>();
</script>

<template>
    <li>
        <Link
            :href="show(id)"
            class="group flex h-full flex-col overflow-hidden rounded-xl border bg-card transition duration-200 hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-lg hover:shadow-primary/5 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
        >
            <div class="relative h-36 overflow-hidden border-b bg-muted/40 px-5 pt-5">
                <div class="h-full rounded-t-md border border-b-0 bg-background px-4 pt-3 shadow-xs transition-transform duration-200 group-hover:translate-y-[-2px]">
                    <p class="truncate text-[0.7rem] font-semibold text-foreground/90">{{ title }}</p>
                    <p
                        v-if="excerpt"
                        class="mt-1.5 line-clamp-5 text-[0.62rem] leading-relaxed text-muted-foreground"
                    >
                        {{ excerpt }}
                    </p>
                    <div v-else class="mt-2 space-y-1.5" aria-hidden="true">
                        <div class="h-1.5 w-11/12 rounded-full bg-muted" />
                        <div class="h-1.5 w-4/5 rounded-full bg-muted" />
                        <div class="h-1.5 w-2/3 rounded-full bg-muted" />
                    </div>
                </div>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-card to-transparent" />
            </div>
            <div class="flex flex-1 items-start justify-between gap-3 px-4 py-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium">{{ title }}</p>
                    <p class="mt-0.5 truncate text-xs text-muted-foreground">
                        <template v-if="meta">{{ meta }} · </template>{{ relativeTime(updatedAt) }}
                    </p>
                </div>
                <slot name="badge" />
            </div>
        </Link>
    </li>
</template>
