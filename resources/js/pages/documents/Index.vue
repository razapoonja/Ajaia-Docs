<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { FileText, Plus, Users } from '@lucide/vue';
import { ref } from 'vue';
import { index, show, store } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import ImportDocumentButton from '@/components/documents/ImportDocumentButton.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { relativeTime } from '@/lib/relativeTime';
import type { OwnedDocumentSummary, SharedDocumentSummary, ShareRole } from '@/types';

defineProps<{
    owned: OwnedDocumentSummary[];
    shared: SharedDocumentSummary[];
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Documents', href: index() }] },
});

const creating = ref(false);

const roleLabel: Record<ShareRole, string> = {
    viewer: 'Can view',
    editor: 'Can edit',
};

function createDocument(): void {
    creating.value = true;
    router.post(store.url(), {}, { onFinish: () => (creating.value = false) });
}
</script>

<template>
    <Head title="Documents" />

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-8 p-4 sm:p-8">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Documents</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Everything you own and everything shared with you.
                </p>
            </div>
            <div class="flex gap-2">
                <ImportDocumentButton />
                <Button :disabled="creating" @click="createDocument">
                    <Plus />
                    New document
                </Button>
            </div>
        </header>

        <section aria-labelledby="owned-heading" class="space-y-3">
            <h2 id="owned-heading" class="text-sm font-medium text-muted-foreground">Your documents</h2>
            <div
                v-if="owned.length === 0"
                class="rounded-xl border border-dashed p-10 text-center"
            >
                <FileText class="mx-auto size-8 text-muted-foreground" />
                <p class="mt-3 font-medium">No documents yet</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Create a blank document or import a .txt, .md or .docx file.
                </p>
            </div>
            <ul v-else class="divide-y overflow-hidden rounded-xl border bg-card">
                <li v-for="document in owned" :key="document.id">
                    <Link
                        :href="show(document.id)"
                        class="flex items-center gap-4 px-4 py-3 transition-colors hover:bg-accent/60 focus-visible:bg-accent/60 focus-visible:outline-none"
                    >
                        <FileText class="size-5 shrink-0 text-muted-foreground" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">{{ document.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                Edited {{ relativeTime(document.updated_at) }}
                            </p>
                        </div>
                        <Badge v-if="document.shares_count > 0" variant="secondary" class="gap-1">
                            <Users />
                            {{ document.shares_count }}
                        </Badge>
                    </Link>
                </li>
            </ul>
        </section>

        <section aria-labelledby="shared-heading" class="space-y-3">
            <h2 id="shared-heading" class="text-sm font-medium text-muted-foreground">Shared with you</h2>
            <p v-if="shared.length === 0" class="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">
                Nothing has been shared with you yet.
            </p>
            <ul v-else class="divide-y overflow-hidden rounded-xl border bg-card">
                <li v-for="document in shared" :key="document.id">
                    <Link
                        :href="show(document.id)"
                        class="flex items-center gap-4 px-4 py-3 transition-colors hover:bg-accent/60 focus-visible:bg-accent/60 focus-visible:outline-none"
                    >
                        <FileText class="size-5 shrink-0 text-muted-foreground" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">{{ document.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ document.owner }} · Edited {{ relativeTime(document.updated_at) }}
                            </p>
                        </div>
                        <Badge variant="outline">{{ roleLabel[document.role] }}</Badge>
                    </Link>
                </li>
            </ul>
        </section>
    </div>
</template>
