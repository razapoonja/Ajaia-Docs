<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { FileText, Plus, Users } from '@lucide/vue';
import { ref } from 'vue';
import { index, store } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import DocumentRow from '@/components/documents/DocumentRow.vue';
import ImportDocumentButton from '@/components/documents/ImportDocumentButton.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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

function sharedWithLabel(count: number): string {
    return count === 1 ? 'Shared with 1 person' : `Shared with ${count} people`;
}

function createDocument(): void {
    creating.value = true;
    router.post(store.url(), {}, { onFinish: () => (creating.value = false) });
}
</script>

<template>
    <Head title="Documents" />

    <div class="flex w-full flex-1 flex-col gap-10 p-4 sm:p-8 lg:px-10">
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
                    {{ creating ? 'Creating…' : 'New document' }}
                </Button>
            </div>
        </header>

        <section aria-labelledby="owned-heading" class="space-y-3">
            <div class="flex items-baseline justify-between">
                <h2 id="owned-heading" class="text-sm font-medium">Your documents</h2>
                <span class="text-xs tabular-nums text-muted-foreground">{{ owned.length }}</span>
            </div>
            <div
                v-if="owned.length === 0"
                class="flex flex-col items-center rounded-xl border border-dashed px-6 py-12 text-center"
            >
                <span class="flex size-12 items-center justify-center rounded-full bg-accent text-accent-foreground">
                    <FileText class="size-5" />
                </span>
                <p class="mt-4 font-medium">Start your first document</p>
                <p class="mt-1 max-w-sm text-sm text-muted-foreground">
                    Create a blank page or import a .txt, .md or .docx file you already have.
                </p>
                <Button class="mt-5" :disabled="creating" @click="createDocument">
                    <Plus />
                    New document
                </Button>
            </div>
            <ul v-else class="divide-y overflow-hidden rounded-xl border bg-card">
                <DocumentRow
                    v-for="document in owned"
                    :key="document.id"
                    :id="document.id"
                    :title="document.title"
                    :updated-at="document.updated_at"
                >
                    <template v-if="document.shares_count > 0" #trailing>
                        <Badge variant="secondary" class="gap-1.5" :title="sharedWithLabel(document.shares_count)">
                            <Users />
                            {{ document.shares_count }}
                        </Badge>
                    </template>
                </DocumentRow>
            </ul>
        </section>

        <section aria-labelledby="shared-heading" class="space-y-3">
            <div class="flex items-baseline justify-between">
                <h2 id="shared-heading" class="text-sm font-medium">Shared with you</h2>
                <span class="text-xs tabular-nums text-muted-foreground">{{ shared.length }}</span>
            </div>
            <p
                v-if="shared.length === 0"
                class="rounded-xl border border-dashed px-6 py-8 text-center text-sm text-muted-foreground"
            >
                When someone shares a document with you, it will appear here.
            </p>
            <ul v-else class="divide-y overflow-hidden rounded-xl border bg-card">
                <DocumentRow
                    v-for="document in shared"
                    :key="document.id"
                    :id="document.id"
                    :title="document.title"
                    :updated-at="document.updated_at"
                    :meta="document.owner"
                >
                    <template #trailing>
                        <Badge variant="outline">{{ roleLabel[document.role] }}</Badge>
                    </template>
                </DocumentRow>
            </ul>
        </section>
    </div>
</template>
