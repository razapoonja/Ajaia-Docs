<script setup lang="ts">
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{ initialUrl: string }>();

const emit = defineEmits<{
    (e: 'save', url: string): void;
    (e: 'remove'): void;
}>();

const url = ref('');
const error = ref('');

watch(open, (isOpen) => {
    if (isOpen) {
        url.value = props.initialUrl;
        error.value = '';
    }
});

/**
 * Accepts "example.com", "https://example.com" or "mailto:a@b.com".
 * Anything with another scheme (javascript:, data:) is rejected.
 */
function normalise(value: string): string | null {
    const trimmed = value.trim();

    if (trimmed === '') {
        return null;
    }

    const withScheme = /^[a-z][a-z0-9+.-]*:/i.test(trimmed) ? trimmed : `https://${trimmed}`;

    try {
        const parsed = new URL(withScheme);

        return ['http:', 'https:', 'mailto:'].includes(parsed.protocol) ? parsed.href : null;
    } catch {
        return null;
    }
}

function submit(): void {
    const normalised = normalise(url.value);

    if (!normalised) {
        error.value = 'Enter a web address like example.com or an email link like mailto:you@example.com.';
        return;
    }

    emit('save', normalised);
    open.value = false;
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ initialUrl ? 'Edit link' : 'Add a link' }}</DialogTitle>
                <DialogDescription>Links open in a new tab.</DialogDescription>
            </DialogHeader>
            <form class="grid gap-2" @submit.prevent="submit">
                <Label for="link-url">Web address</Label>
                <Input id="link-url" v-model="url" v-focus placeholder="example.com" autocomplete="url" :aria-invalid="Boolean(error)" />
                <InputError :message="error" />
                <DialogFooter class="mt-2 gap-2 sm:justify-between">
                    <Button v-if="initialUrl" type="button" variant="ghost" class="text-destructive" @click="emit('remove'); open = false">
                        Remove link
                    </Button>
                    <span v-else />
                    <Button type="submit">Save link</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
