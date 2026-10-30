<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Bold,
    Check,
    Eye,
    Heading1,
    Heading2,
    Italic,
    List,
    ListOrdered,
    Share2,
    Trash2,
    Underline,
} from '@lucide/vue';
import { index as documents } from '@/actions/App/Http/Controllers/Documents/DocumentController';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';

const toolbar = [
    Bold,
    Italic,
    Underline,
    Heading1,
    Heading2,
    List,
    ListOrdered,
];

const people = [
    {
        initials: 'AO',
        name: 'Alice',
        email: 'alice@example.com',
        role: 'Owner',
        sentence:
            'Owns the roadmap. Can edit, share and delete it, and open the admin panel.',
        dot: 'bg-primary',
        canEdit: true,
        canShare: true,
    },
    {
        initials: 'BE',
        name: 'Bob',
        email: 'bob@example.com',
        role: 'Editor',
        sentence: 'Can change the text. Cannot share it onward or delete it.',
        dot: 'bg-emerald-600',
        canEdit: true,
        canShare: false,
    },
    {
        initials: 'CV',
        name: 'Carol',
        email: 'carol@example.com',
        role: 'Viewer',
        sentence: 'Can read it. The editor opens read-only, with no toolbar.',
        dot: 'bg-violet-600',
        canEdit: false,
        canShare: false,
    },
];

const details = [
    {
        term: 'Autosave',
        description:
            'Saves 800 ms after you stop typing. Ctrl+S saves straight away, and the page warns you before you leave with unsaved changes.',
    },
    {
        term: 'Import',
        description:
            'Turn a .txt, .md or .docx file into a new document. Files are read in your browser and only the text is uploaded.',
    },
    {
        term: 'Export',
        description:
            'Download any document you can open as Markdown or a web page, or print it to PDF.',
    },
    {
        term: 'Access checks',
        description:
            'Every request is checked on the server against one policy. A hidden button is never the only thing stopping someone.',
    },
    {
        term: 'Admin panel',
        description:
            'Dashboards, filters and bulk actions for people, documents and shares, for admins only.',
    },
    {
        term: 'Light and dark',
        description:
            'Light by default. Switch to dark in your appearance settings.',
    },
];
</script>

<template>
    <Head title="Write together" />

    <div class="min-h-dvh bg-muted text-foreground">
        <header
            class="flex items-center justify-between px-4 py-5 sm:px-8 lg:px-12"
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
            <nav class="flex items-center gap-2" aria-label="Account">
                <Button v-if="$page.props.auth.user" as-child>
                    <Link :href="documents()">Open my documents</Link>
                </Button>
                <template v-else>
                    <Button variant="ghost" as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">Create an account</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main>
            <!-- Hero: the page is a document -->
            <section
                class="px-4 pt-2 pb-16 sm:px-8 sm:pt-6 sm:pb-24 lg:px-12 lg:pt-10"
                aria-labelledby="hero-title"
            >
                <div
                    class="mx-auto grid max-w-6xl gap-6 xl:grid-cols-[minmax(0,1fr)_15rem]"
                >
                    <div>
                        <div
                            class="mb-3 flex items-center justify-end gap-4 px-1 sm:justify-between"
                        >
                            <p
                                class="hidden truncate text-sm text-muted-foreground sm:block"
                            >
                                Welcome to Ajaia Docs
                            </p>
                            <div class="flex items-center gap-3">
                                <div class="flex -space-x-2" aria-hidden="true">
                                    <span
                                        v-for="person in people"
                                        :key="person.initials"
                                        :class="person.dot"
                                        class="flex size-7 items-center justify-center rounded-full border-2 border-muted text-[0.6rem] font-semibold text-white"
                                    >
                                        {{ person.initials }}
                                    </span>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <Link
                                        :href="
                                            $page.props.auth.user
                                                ? documents()
                                                : login()
                                        "
                                    >
                                        <Share2 />
                                        Share
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <article
                            class="paper overflow-hidden rounded-xl bg-card"
                        >
                            <div
                                class="flex items-center gap-0.5 border-b px-3 py-2 text-muted-foreground"
                                aria-hidden="true"
                            >
                                <span
                                    v-for="(icon, i) in toolbar"
                                    :key="i"
                                    class="flex size-8 items-center justify-center rounded-md"
                                    :class="
                                        i === 3
                                            ? 'bg-accent text-accent-foreground'
                                            : ''
                                    "
                                >
                                    <component :is="icon" class="size-4" />
                                </span>
                            </div>

                            <div
                                class="px-5 py-10 sm:px-14 sm:py-16 lg:px-20 lg:py-20"
                            >
                                <h1
                                    id="hero-title"
                                    class="max-w-[14ch] text-[2.6rem] leading-[1.04] font-semibold tracking-[-0.035em] sm:text-6xl lg:text-7xl"
                                >
                                    Write together, without the clutter.<span
                                        class="caret"
                                        aria-hidden="true"
                                    />
                                </h1>

                                <p
                                    class="mt-8 max-w-[58ch] text-lg leading-relaxed text-muted-foreground"
                                >
                                    A small document editor for teams. Write
                                    with real formatting, bring in the files you
                                    already have, and decide who can read or
                                    change each one.
                                </p>

                                <ul
                                    class="mt-8 max-w-[62ch] list-disc space-y-3 pl-5 marker:text-muted-foreground/60"
                                >
                                    <li>
                                        <strong class="font-semibold"
                                            >Formatting that sticks.</strong
                                        >
                                        Headings, lists, bold, italic and
                                        underline survive every save and
                                        refresh.
                                    </li>
                                    <li>
                                        <strong class="font-semibold"
                                            >Your files, imported.</strong
                                        >
                                        Drop in a .txt, .md or .docx file and
                                        keep writing.
                                    </li>
                                    <li>
                                        <strong class="font-semibold"
                                            >Access you choose.</strong
                                        >
                                        Share by email as a viewer or an editor,
                                        and revoke it whenever you like.
                                    </li>
                                </ul>

                                <div class="mt-12 flex flex-wrap gap-3">
                                    <Button
                                        v-if="$page.props.auth.user"
                                        size="lg"
                                        as-child
                                    >
                                        <Link :href="documents()"
                                            >Open my documents</Link
                                        >
                                    </Button>
                                    <template v-else>
                                        <Button size="lg" as-child>
                                            <Link :href="register()"
                                                >Start writing</Link
                                            >
                                        </Button>
                                        <Button
                                            size="lg"
                                            variant="outline"
                                            as-child
                                        >
                                            <Link :href="login()"
                                                >Log in with a demo
                                                account</Link
                                            >
                                        </Button>
                                    </template>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between border-t bg-muted/40 px-5 py-2 text-xs text-muted-foreground"
                                aria-hidden="true"
                            >
                                <span class="tabular-nums">78 words</span>
                                <span class="inline-flex items-center gap-1"
                                    ><Check class="size-3" /> Saved</span
                                >
                            </div>
                        </article>
                    </div>

                    <!-- Margin notes, like comments beside a page -->
                    <aside
                        class="hidden space-y-3 pt-24 xl:block"
                        aria-label="Who has access"
                    >
                        <div
                            v-for="person in people"
                            :key="person.name"
                            class="relative rounded-lg border bg-card p-3 text-sm shadow-xs"
                        >
                            <span
                                class="absolute top-5 -left-6 h-px w-6 bg-border"
                                aria-hidden="true"
                            />
                            <div class="flex items-center gap-2">
                                <span
                                    :class="person.dot"
                                    class="flex size-6 items-center justify-center rounded-full text-[0.55rem] font-semibold text-white"
                                    >{{ person.initials }}</span
                                >
                                <span class="font-medium">{{
                                    person.name
                                }}</span>
                                <span
                                    class="ml-auto text-xs text-muted-foreground"
                                    >{{ person.role }}</span
                                >
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <!-- One document, three people -->
            <section
                class="border-t bg-background px-4 py-16 sm:px-8 sm:py-24 lg:px-12"
                aria-labelledby="roles-title"
            >
                <div class="mx-auto max-w-6xl">
                    <div class="max-w-2xl">
                        <h2
                            id="roles-title"
                            class="text-3xl font-semibold tracking-tight sm:text-4xl"
                        >
                            One document, three people
                        </h2>
                        <p
                            class="mt-4 text-lg leading-relaxed text-muted-foreground"
                        >
                            Every document has one owner, who decides who else
                            can open it and whether they can change it. Log in
                            as each person below to see the same page from their
                            side. They all use the password
                            <code
                                class="rounded bg-muted px-1.5 py-0.5 font-mono text-base text-foreground"
                                >password</code
                            >.
                        </p>
                    </div>

                    <ul
                        class="mt-10 grid gap-10 sm:mt-14 md:grid-cols-3 md:gap-6"
                    >
                        <li v-for="person in people" :key="person.name">
                            <div
                                class="rounded-xl border bg-card shadow-xs"
                                aria-hidden="true"
                            >
                                <div
                                    class="flex items-center justify-between gap-2 border-b px-3 py-2"
                                >
                                    <div
                                        v-if="person.canEdit"
                                        class="flex gap-1 text-muted-foreground"
                                    >
                                        <Bold class="size-3.5" /><Italic
                                            class="size-3.5"
                                        /><List class="size-3.5" />
                                    </div>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
                                    >
                                        <Eye class="size-3.5" /> View only
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            v-if="person.canShare"
                                            class="inline-flex items-center gap-1 rounded border px-1.5 py-0.5 text-[0.65rem] font-medium"
                                        >
                                            <Share2 class="size-3" /> Share
                                        </span>
                                        <Trash2
                                            v-if="person.canShare"
                                            class="size-3.5 text-muted-foreground"
                                        />
                                        <span
                                            v-if="!person.canShare"
                                            class="rounded border px-1.5 py-0.5 text-[0.65rem] text-muted-foreground"
                                        >
                                            {{
                                                person.canEdit
                                                    ? 'Can edit'
                                                    : 'Can view'
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-2 px-5 py-6">
                                    <p class="font-semibold tracking-tight">
                                        Q4 product roadmap
                                    </p>
                                    <div
                                        class="h-2 w-11/12 rounded-full bg-muted"
                                    />
                                    <div
                                        class="h-2 w-4/5 rounded-full bg-muted"
                                    />
                                    <div
                                        class="h-2 w-3/5 rounded-full bg-muted"
                                    />
                                </div>
                            </div>
                            <div class="mt-5 flex items-center gap-2.5">
                                <span
                                    :class="person.dot"
                                    class="flex size-7 items-center justify-center rounded-full text-[0.6rem] font-semibold text-white"
                                    >{{ person.initials }}</span
                                >
                                <p class="font-medium">
                                    {{ person.name }},
                                    {{ person.role.toLowerCase() }}
                                </p>
                            </div>
                            <p
                                class="mt-2 text-sm leading-relaxed text-muted-foreground"
                            >
                                {{ person.sentence }}
                            </p>
                            <p class="mt-3 font-mono text-sm">
                                {{ person.email }}
                            </p>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Details -->
            <section
                class="border-t bg-background px-4 py-16 sm:px-8 sm:py-24 lg:px-12"
                aria-labelledby="details-title"
            >
                <div
                    class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[18rem_minmax(0,1fr)]"
                >
                    <h2
                        id="details-title"
                        class="text-3xl font-semibold tracking-tight"
                    >
                        The rest of it
                    </h2>
                    <dl class="grid gap-x-12 sm:grid-cols-2">
                        <div
                            v-for="item in details"
                            :key="item.term"
                            class="border-t py-6"
                        >
                            <dt class="font-semibold">{{ item.term }}</dt>
                            <dd
                                class="mt-2 max-w-[48ch] text-sm leading-relaxed text-muted-foreground"
                            >
                                {{ item.description }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="border-t px-4 py-14 sm:px-8 sm:py-20 lg:px-12">
                <div
                    class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 sm:flex-row sm:items-center"
                >
                    <p class="text-2xl font-semibold tracking-tight">
                        Start with a blank page.
                    </p>
                    <Button size="lg" as-child>
                        <Link
                            :href="
                                $page.props.auth.user ? documents() : register()
                            "
                        >
                            {{
                                $page.props.auth.user
                                    ? 'Open my documents'
                                    : 'Create an account'
                            }}
                        </Link>
                    </Button>
                </div>
            </section>
        </main>

        <footer
            class="border-t px-4 py-8 text-sm text-muted-foreground sm:px-8 lg:px-12"
        >
            <div
                class="mx-auto flex max-w-6xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-6 items-center justify-center rounded-md bg-primary text-primary-foreground"
                    >
                        <AppLogoIcon class="size-3.5" />
                    </span>
                    Ajaia Docs
                </div>
                <nav class="flex flex-wrap gap-x-6 gap-y-2" aria-label="Footer">
                    <a
                        href="https://github.com/razapoonja/Ajaia-Docs"
                        class="transition-colors hover:text-foreground"
                        target="_blank"
                        rel="noopener noreferrer"
                        >Source code</a
                    >
                    <a
                        href="/admin"
                        class="transition-colors hover:text-foreground"
                        >Admin panel</a
                    >
                    <Link
                        :href="login()"
                        class="transition-colors hover:text-foreground"
                        >Log in</Link
                    >
                </nav>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.paper {
    box-shadow:
        0 1px 0 0 var(--border),
        0 1px 2px rgb(15 23 42 / 0.06),
        0 12px 32px -8px rgb(15 23 42 / 0.14),
        0 40px 80px -24px rgb(37 99 235 / 0.12);
}

.caret {
    display: inline-block;
    width: 0.06em;
    height: 0.82em;
    margin-left: 0.06em;
    vertical-align: -0.04em;
    background: var(--primary);
    animation: blink 1.1s steps(1) infinite;
}

@keyframes blink {
    50% {
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .caret {
        animation: none;
    }
}
</style>
