<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { FileText, Share2, Upload } from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();

const points = [
    { icon: FileText, text: 'Rich text that saves as you type' },
    { icon: Upload, text: 'Import .txt, .md and .docx files' },
    { icon: Share2, text: 'Share as viewer or editor, revoke any time' },
];

const demoAccounts = [
    { email: 'alice@example.com', note: 'Owner · admin' },
    { email: 'bob@example.com', note: 'Can edit' },
    { email: 'carol@example.com', note: 'Can view' },
];
</script>

<template>
    <div class="grid min-h-dvh lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
        <aside class="relative hidden flex-col justify-between overflow-hidden bg-primary p-10 text-primary-foreground lg:flex xl:p-14">
            <div
                class="pointer-events-none absolute -top-32 -right-32 size-[28rem] rounded-full bg-white/10 blur-3xl"
                aria-hidden="true"
            />
            <div
                class="pointer-events-none absolute -bottom-40 -left-24 size-[26rem] rounded-full bg-black/10 blur-3xl"
                aria-hidden="true"
            />

            <Link :href="home()" class="relative z-10 flex items-center gap-2.5 text-lg font-semibold tracking-tight">
                <span class="flex size-9 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/20">
                    <AppLogoIcon class="size-5" />
                </span>
                {{ name }}
            </Link>

            <div class="relative z-10 max-w-md space-y-8">
                <div class="space-y-3">
                    <h2 class="text-3xl font-semibold tracking-tight xl:text-4xl">
                        Write together, without the clutter.
                    </h2>
                    <p class="text-primary-foreground/80">
                        A lightweight document editor for teams.
                    </p>
                </div>
                <ul class="space-y-3 text-sm">
                    <li v-for="point in points" :key="point.text" class="flex items-center gap-3">
                        <component :is="point.icon" class="size-4 shrink-0 text-primary-foreground/80" aria-hidden="true" />
                        <span>{{ point.text }}</span>
                    </li>
                </ul>
            </div>

            <div class="relative z-10 rounded-xl bg-white/10 p-4 text-sm ring-1 ring-white/15">
                <p class="font-medium">Reviewing this build?</p>
                <p class="mt-0.5 text-xs text-primary-foreground/75">
                    All demo accounts use the password
                    <code class="rounded bg-black/20 px-1 py-0.5 font-mono">password</code>
                </p>
                <ul class="mt-3 space-y-1.5">
                    <li
                        v-for="account in demoAccounts"
                        :key="account.email"
                        class="flex items-center justify-between gap-3 text-xs"
                    >
                        <code class="font-mono">{{ account.email }}</code>
                        <span class="text-primary-foreground/75">{{ account.note }}</span>
                    </li>
                </ul>
            </div>
        </aside>

        <div class="flex flex-col bg-background">
            <div class="flex items-center justify-between px-6 py-5 lg:hidden">
                <Link :href="home()" class="flex items-center gap-2 font-semibold tracking-tight">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <AppLogoIcon class="size-4" />
                    </span>
                    {{ name }}
                </Link>
            </div>
            <div class="flex flex-1 items-center justify-center px-6 py-10 sm:px-10">
                <div class="w-full max-w-sm space-y-8">
                    <div class="space-y-1.5">
                        <h1 v-if="title" class="text-2xl font-semibold tracking-tight">
                            {{ title }}
                        </h1>
                        <p v-if="description" class="text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
