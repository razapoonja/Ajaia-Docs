<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Share2, Upload } from '@lucide/vue';
import { index as documents } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';

const demoAccounts = [
    { email: 'alice@example.com', note: 'Owns the shared roadmap, admin' },
    { email: 'bob@example.com', note: 'Can edit the roadmap' },
    { email: 'carol@example.com', note: 'Can only view the roadmap' },
];
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-dvh flex-col bg-background text-foreground">
        <header class="mx-auto flex w-full max-w-5xl items-center justify-between px-4 py-5 sm:px-8">
            <div class="flex items-center gap-2 font-semibold">
                <AppLogoIcon class="size-6 fill-current" />
                Ajaia Docs
            </div>
            <nav class="flex items-center gap-2">
                <Button v-if="$page.props.auth.user" as-child>
                    <Link :href="documents()">Open documents</Link>
                </Button>
                <template v-else>
                    <Button variant="ghost" as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">Create account</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main class="mx-auto flex w-full max-w-5xl flex-1 flex-col justify-center gap-12 px-4 py-12 sm:px-8">
            <section class="max-w-2xl space-y-5">
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl">
                    Write together, without the clutter.
                </h1>
                <p class="text-lg text-muted-foreground">
                    A lightweight document editor for teams. Create, format, import, and share documents with the people who need them.
                </p>
                <div v-if="!$page.props.auth.user" class="flex flex-wrap gap-3">
                    <Button size="lg" as-child>
                        <Link :href="register()">Start writing</Link>
                    </Button>
                    <Button size="lg" variant="outline" as-child>
                        <Link :href="login()">I have an account</Link>
                    </Button>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-5">
                    <FileText class="size-5 text-primary" />
                    <h2 class="mt-3 font-medium">Rich text that persists</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Headings, lists, bold, italic and underline. Saved as you type.</p>
                </div>
                <div class="rounded-xl border bg-card p-5">
                    <Upload class="size-5 text-primary" />
                    <h2 class="mt-3 font-medium">Import existing files</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Drop in a .txt, .md or .docx and keep editing it here.</p>
                </div>
                <div class="rounded-xl border bg-card p-5">
                    <Share2 class="size-5 text-primary" />
                    <h2 class="mt-3 font-medium">Share with intent</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Give someone view or edit access. Revoke it any time.</p>
                </div>
            </section>

            <section class="max-w-xl rounded-xl border border-dashed p-5 text-sm">
                <p class="font-medium">Reviewing this build?</p>
                <p class="mt-1 text-muted-foreground">
                    Three seeded accounts share the password
                    <code class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">password</code>
                </p>
                <ul class="mt-4 divide-y rounded-lg border bg-card">
                    <li
                        v-for="account in demoAccounts"
                        :key="account.email"
                        class="flex flex-col gap-0.5 px-3 py-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <code class="font-mono text-xs text-foreground">{{ account.email }}</code>
                        <span class="text-xs text-muted-foreground">{{ account.note }}</span>
                    </li>
                </ul>
            </section>
        </main>
    </div>
</template>
