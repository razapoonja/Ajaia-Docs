<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Share2, X } from '@lucide/vue';
import { ref } from 'vue';
import { destroy as destroyShare, store as storeShare } from '@/actions/App/Http/Controllers/Documents/DocumentShareController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DocumentShare, ShareRole } from '@/types';

const props = defineProps<{
    documentId: number;
    shares: DocumentShare[];
}>();

const open = ref(false);
const removingId = ref<number | null>(null);
const updatingId = ref<number | null>(null);

const form = useForm<{ email: string; role: ShareRole }>({
    email: '',
    role: 'viewer',
});

function submit(): void {
    form.post(storeShare.url({ document: props.documentId }), {
        preserveScroll: true,
        onSuccess: () => form.reset('email'),
    });
}

function changeRole(share: DocumentShare, role: ShareRole): void {
    if (role === share.role) {
        return;
    }

    updatingId.value = share.id;
    router.post(
        storeShare.url({ document: props.documentId }),
        { email: share.user.email, role },
        { preserveScroll: true, onFinish: () => (updatingId.value = null) },
    );
}

function remove(share: DocumentShare): void {
    removingId.value = share.id;
    router.delete(destroyShare.url({ document: props.documentId, share: share.id }), {
        preserveScroll: true,
        onFinish: () => (removingId.value = null),
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
                <Share2 />
                Share
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Share this document</DialogTitle>
                <DialogDescription>
                    People you add can open it from their own account. Editors can change the text; viewers can only read.
                </DialogDescription>
            </DialogHeader>

            <form class="grid gap-3" @submit.prevent="submit">
                <div class="grid gap-1.5">
                    <Label for="share-email">Email address</Label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Input
                            id="share-email"
                            v-model="form.email"
                            type="email"
                            autocomplete="off"
                            placeholder="bob@example.com"
                            required
                            :aria-invalid="Boolean(form.errors.email)"
                        />
                        <Select v-model="form.role">
                            <SelectTrigger class="sm:w-36" aria-label="Access level">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="viewer">Can view</SelectItem>
                                <SelectItem value="editor">Can edit</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <InputError :message="form.errors.email ?? form.errors.role" />
                </div>
                <Button type="submit" :disabled="form.processing" class="sm:justify-self-end">
                    {{ form.processing ? 'Sharing…' : 'Share' }}
                </Button>
            </form>

            <div class="space-y-2">
                <p class="text-sm font-medium">People with access</p>
                <p v-if="shares.length === 0" class="text-sm text-muted-foreground">
                    Only you can see this document.
                </p>
                <ul v-else class="divide-y rounded-lg border">
                    <li
                        v-for="share in shares"
                        :key="share.id"
                        class="flex items-center justify-between gap-3 px-3 py-2"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ share.user.name }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ share.user.email }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <Select
                                :model-value="share.role"
                                :disabled="updatingId === share.id"
                                @update:model-value="(role) => changeRole(share, role as ShareRole)"
                            >
                                <SelectTrigger size="sm" class="h-8 w-28 text-xs" :aria-label="`Access level for ${share.user.name}`">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="viewer">Can view</SelectItem>
                                    <SelectItem value="editor">Can edit</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon-sm"
                                :aria-label="`Remove access for ${share.user.name}`"
                                :disabled="removingId === share.id"
                                @click="remove(share)"
                            >
                                <X />
                            </Button>
                        </div>
                    </li>
                </ul>
            </div>
        </DialogContent>
    </Dialog>
</template>
