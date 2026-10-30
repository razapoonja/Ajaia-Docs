<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { FileText, Plus, Search, Users } from '@lucide/vue';
import { computed, ref } from 'vue';
import { index, store } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import DocumentCard from '@/components/documents/DocumentCard.vue';
import ImportDocumentButton from '@/components/documents/ImportDocumentButton.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { OwnedDocumentSummary, SharedDocumentSummary, ShareRole } from '@/types';

const props = defineProps<{
    owned: OwnedDocumentSummary[];
    shared: SharedDocumentSummary[];
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Documents', href: index() }] },
});

type Scope = 'all' | 'owned' | 'shared';

const page = usePage();
const creating = ref(false);
const query = ref('');
const scope = ref<Scope>('all');

const roleLabel: Record<ShareRole, string> = {
    viewer: 'Can view',
    editor: 'Can edit',
};

const firstName = computed(() => String(page.props.auth.user.name).split(' ')[0]);

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    return hour < 18 ? 'Good afternoon' : 'Good evening';
});

function matches(document: { title: string; excerpt: string }): boolean {
    const needle = query.value.trim().toLowerCase();

    return needle === '' || `${document.title} ${document.excerpt}`.toLowerCase().includes(needle);
}

const visibleOwned = computed(() => (scope.value === 'shared' ? [] : props.owned.filter(matches)));
const visibleShared = computed(() => (scope.value === 'owned' ? [] : props.shared.filter(matches)));
const isSearching = computed(() => query.value.trim() !== '');
const nothingFound = computed(() => isSearching.value && visibleOwned.value.length === 0 && visibleShared.value.length === 0);

const scopes: Array<{ value: Scope; label: string }> = [
    { value: 'all', label: 'All' },
    { value: 'owned', label: 'Owned by me' },
    { value: 'shared', label: 'Shared with me' },
];

function createDocument(): void {
    creating.value = true;
    router.post(store.url(), {}, { onFinish: () => (creating.value = false) });
}
</script>

<template>
    <Head title="Documents" />

    <div class="flex w-full flex-1 flex-col gap-8 p-4 sm:p-8 lg:px-10">
        <header class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm text-muted-foreground">{{ greeting }}, {{ firstName }}</p>
                <h1 class="mt-1 text-3xl font-semibold tracking-tight">Your documents</h1>
            </div>
            <div class="flex gap-2">
                <ImportDocumentButton />
                <Button :disabled="creating" @click="createDocument">
                    <Plus />
                    {{ creating ? 'Creating…' : 'New document' }}
                </Button>
            </div>
        </header>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <label class="relative block w-full sm:max-w-sm">
                <span class="sr-only">Search documents</span>
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <input
                    v-model="query"
                    type="search"
                    placeholder="Search by title or text"
                    class="h-10 w-full rounded-lg border bg-card pr-3 pl-9 text-sm shadow-xs outline-none transition placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/30"
                />
            </label>
            <div role="tablist" aria-label="Filter documents" class="inline-flex rounded-lg border bg-card p-1 shadow-xs">
                <button
                    v-for="option in scopes"
                    :key="option.value"
                    type="button"
                    role="tab"
                    :aria-selected="scope === option.value"
                    class="cursor-pointer rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                    :class="scope === option.value ? 'bg-primary text-primary-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
                    @click="scope = option.value"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>

        <p v-if="nothingFound" class="rounded-xl border border-dashed py-12 text-center text-sm text-muted-foreground">
            Nothing matches "{{ query }}".
        </p>

        <section v-if="scope !== 'shared' && !nothingFound" aria-labelledby="owned-heading" class="space-y-4">
            <div class="flex items-baseline justify-between">
                <h2 id="owned-heading" class="text-sm font-medium">Owned by me</h2>
                <span class="text-xs tabular-nums text-muted-foreground">{{ visibleOwned.length }}</span>
            </div>
            <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                <li v-if="!isSearching">
                    <button
                        type="button"
                        :disabled="creating"
                        class="group flex h-full min-h-56 w-full cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border border-dashed bg-card/50 text-muted-foreground transition hover:border-primary/50 hover:bg-accent/40 hover:text-primary disabled:cursor-wait"
                        @click="createDocument"
                    >
                        <span class="flex size-12 items-center justify-center rounded-full bg-accent text-accent-foreground transition-transform group-hover:scale-105">
                            <Plus class="size-5" />
                        </span>
                        <span class="text-sm font-medium">Blank document</span>
                    </button>
                </li>
                <DocumentCard
                    v-for="document in visibleOwned"
                    :key="document.id"
                    :id="document.id"
                    :title="document.title"
                    :excerpt="document.excerpt"
                    :updated-at="document.updated_at"
                >
                    <template v-if="document.shares_count > 0" #badge>
                        <Badge variant="secondary" class="shrink-0 gap-1" :title="`Shared with ${document.shares_count}`">
                            <Users />
                            {{ document.shares_count }}
                        </Badge>
                    </template>
                </DocumentCard>
            </ul>
        </section>

        <section v-if="scope !== 'owned' && !nothingFound" aria-labelledby="shared-heading" class="space-y-4">
            <div class="flex items-baseline justify-between">
                <h2 id="shared-heading" class="text-sm font-medium">Shared with me</h2>
                <span class="text-xs tabular-nums text-muted-foreground">{{ visibleShared.length }}</span>
            </div>
            <div
                v-if="visibleShared.length === 0"
                class="flex flex-col items-center rounded-xl border border-dashed px-6 py-10 text-center"
            >
                <span class="flex size-10 items-center justify-center rounded-full bg-muted text-muted-foreground">
                    <FileText class="size-5" />
                </span>
                <p class="mt-3 text-sm font-medium">Nothing shared with you yet</p>
                <p class="mt-1 text-sm text-muted-foreground">When someone shares a document with you, it shows up here.</p>
            </div>
            <ul v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                <DocumentCard
                    v-for="document in visibleShared"
                    :key="document.id"
                    :id="document.id"
                    :title="document.title"
                    :excerpt="document.excerpt"
                    :updated-at="document.updated_at"
                    :meta="document.owner"
                >
                    <template #badge>
                        <Badge variant="outline" class="shrink-0">{{ roleLabel[document.role] }}</Badge>
                    </template>
                </DocumentCard>
            </ul>
        </section>
    </div>
</template>
