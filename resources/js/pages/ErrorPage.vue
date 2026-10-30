<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{ status: number }>();

const copy: Record<number, { title: string; body: string }> = {
    403: {
        title: "You don't have access to this document",
        body: 'Ask the owner to share it with you, or open one of your own documents.',
    },
    404: {
        title: 'This page does not exist',
        body: 'It may have been deleted, or the link is wrong.',
    },
    419: {
        title: 'Your session expired',
        body: 'Reload the page and try again.',
    },
    429: {
        title: 'Too many requests',
        body: 'Wait a minute, then try again.',
    },
    500: {
        title: 'Something went wrong on our side',
        body: 'Try again in a moment. If it keeps happening, the error has been logged.',
    },
    503: {
        title: 'Back shortly',
        body: 'Ajaia Docs is being updated. Try again in a few minutes.',
    },
};

const message = computed(() => copy[props.status] ?? copy[500]);
</script>

<template>
    <Head :title="message.title" />

    <div
        class="flex min-h-dvh flex-col bg-muted px-4 py-6 text-foreground sm:px-8"
    >
        <Link
            href="/"
            class="flex items-center gap-2.5 font-semibold tracking-tight"
        >
            <span
                class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground"
            >
                <AppLogoIcon class="size-4.5" />
            </span>
            Ajaia Docs
        </Link>

        <main class="flex flex-1 items-center justify-center py-12">
            <div
                class="w-full max-w-lg rounded-xl border bg-card p-8 shadow-sm sm:p-10"
            >
                <p class="font-mono text-sm text-muted-foreground">
                    Error {{ status }}
                </p>
                <h1
                    class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl"
                >
                    {{ message.title }}
                </h1>
                <p class="mt-3 text-muted-foreground">{{ message.body }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Button as-child>
                        <Link href="/documents">Go to my documents</Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/">Home</Link>
                    </Button>
                </div>
            </div>
        </main>
    </div>
</template>
