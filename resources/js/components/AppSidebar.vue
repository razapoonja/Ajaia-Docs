<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    FileText,
    Files,
    Plus,
    ShieldCheck,
    UserRound,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import {
    index as documents,
    show,
    store,
} from '@/actions/App/Http/Controllers/Documents/DocumentController';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

const page = usePage();
const creating = ref(false);

const counts = computed(
    () => page.props.sidebar?.counts ?? { owned: 0, shared: 0 },
);
const recent = computed(() => page.props.sidebar?.recent ?? []);

const currentPath = computed(() => page.url.split('?')[0]);
const currentScope = computed(
    () =>
        new URLSearchParams(page.url.split('?')[1] ?? '').get('scope') ?? 'all',
);

const library = computed(() => [
    {
        title: 'All documents',
        scope: 'all',
        icon: Files,
        count: counts.value.owned + counts.value.shared,
    },
    {
        title: 'Owned by me',
        scope: 'owned',
        icon: UserRound,
        count: counts.value.owned,
    },
    {
        title: 'Shared with me',
        scope: 'shared',
        icon: Users,
        count: counts.value.shared,
    },
]);

function libraryHref(scope: string): string {
    return scope === 'all'
        ? documents.url()
        : documents.url({ query: { scope } });
}

function isLibraryActive(scope: string): boolean {
    return (
        currentPath.value === documents.url() && currentScope.value === scope
    );
}

function createDocument(): void {
    creating.value = true;
    router.post(store.url(), {}, { onFinish: () => (creating.value = false) });
}
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="documents()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem class="mt-1">
                    <SidebarMenuButton
                        tooltip="New document"
                        :disabled="creating"
                        class="cursor-pointer border bg-background font-medium shadow-xs hover:bg-accent"
                        @click="createDocument"
                    >
                        <Plus />
                        <span>{{
                            creating ? 'Creating…' : 'New document'
                        }}</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup>
                <SidebarGroupLabel>Library</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in library" :key="item.scope">
                        <SidebarMenuButton
                            as-child
                            :is-active="isLibraryActive(item.scope)"
                            :tooltip="item.title"
                        >
                            <Link :href="libraryHref(item.scope)">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                        <SidebarMenuBadge class="tabular-nums">{{
                            item.count
                        }}</SidebarMenuBadge>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup
                v-if="recent.length"
                class="group-data-[collapsible=icon]:hidden"
            >
                <SidebarGroupLabel>Recent</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem
                        v-for="document in recent"
                        :key="document.id"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="currentPath === show.url(document.id)"
                            :tooltip="document.title"
                        >
                            <Link :href="show(document.id)">
                                <FileText />
                                <span class="truncate">{{
                                    document.title
                                }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu v-if="page.props.auth.isAdmin">
                <SidebarMenuItem>
                    <SidebarMenuButton as-child tooltip="Admin panel">
                        <a href="/admin">
                            <ShieldCheck />
                            <span>Admin panel</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
