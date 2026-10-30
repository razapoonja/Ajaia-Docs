# Ajaia Docs

A lightweight collaborative document editor, built for the Ajaia Full Stack Product Engineer assessment. Write rich-text documents, import the files you already have, and share each document with other people as a viewer or an editor.

- **Live app:** https://ajaia-docs.razapoonja.com
- **Admin panel:** https://ajaia-docs.razapoonja.com/admin
- **Test accounts:** see [below](#test-accounts). Every account uses the password `password`.

## Features

**Documents**

- Create, rename (inline), edit, copy and delete documents
- Autosave 800 ms after you stop typing, with a visible save state. Ctrl/Cmd+S saves straight away, and the app warns before you leave with unsaved changes
- Word count, character count and reading time
- Export as Markdown, a standalone HTML page, or print to PDF

**Editor (Tiptap 3)**

- Headings 1–3, bold, italic, underline, strikethrough, inline code, links
- Bullet, numbered and checklist lists, quotes, code blocks, dividers
- Toolbar with keyboard shortcuts, a bubble menu on selected text, Ctrl/Cmd+K for links
- Markdown-style shortcuts while typing: `#`, `-`, `1.`, `[ ]`, `>`, ` ``` `

**Import**

- `.txt`, `.md` and `.docx` become a new, editable document. Files are read in the browser and only the text is uploaded
- 5 MB limit. Other file types are rejected with a message that says what is supported

**Sharing**

- One owner per document. The owner shares by email as **Can view** or **Can edit**, changes the role inline, or revokes access
- "Owned by me" and "Shared with me" are separate, with role badges, counts and search
- Viewers get a read-only editor with no toolbar. Editors can change content but cannot share or delete

**Admin panel (Filament 5)**

- Dashboard: stats with 7-day trends, activity and access charts, top owners, recently edited
- Documents, users and shares with view pages, relation managers, filters, tabs, grouping, global search (Ctrl/Cmd+K) and bulk actions: transfer ownership, change access, grant or revoke admin, delete

**Quality**

- Server-side authorization on every document route (`DocumentPolicy`)
- Document HTML sanitized on every write against an allowlist of what the editor produces
- 44 feature tests. Responsive from 375 px phones up. Light and dark themes

## Stack

Laravel 13, Inertia 3, Vue 3 with TypeScript, Tailwind 4, shadcn-vue, Tiptap 3, Filament 5, SQLite, Pest.

## Run it locally

Requirements: PHP 8.4+ (the locked Symfony 8 packages need 8.4.1), Composer, Node 20+.

```bash
git clone https://github.com/razapoonja/Ajaia-Docs.git ajaia-docs
cd ajaia-docs
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # Windows: type nul > database\database.sqlite
php artisan migrate:fresh --seed
npm run build                         # also generates the typed route helpers
php artisan serve
```

Open http://localhost:8000. For hot reload, run `npm run dev` in a second terminal instead of `npm run build`.

## Test accounts

All accounts use the password `password`.

| Email             | What they show                                                                                                                                                                 |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| alice@example.com | Owns eight documents, including "Q4 product roadmap" shared with Bob and Carol. Three documents are shared with her. Admin: can open `/admin`. Best account for a walkthrough. |
| bob@example.com   | Can **edit** the roadmap: toolbar present, no Share or Delete. Owns "Meeting notes".                                                                                           |
| carol@example.com | Can **view** the roadmap only: read-only banner, no toolbar.                                                                                                                   |

Eight named teammates (maya@, daniel@, priya@ and others, all `@example.com`) and their documents are spread over the last 30 days so the admin dashboard has real history. They share some documents with Alice, never with Bob or Carol.

A five-minute review:

1. Log in as Alice. Open "Q4 product roadmap", edit it, watch the save state, reload.
2. Open **Share** and change Carol to "Can edit", then back.
3. Import a `.md` or `.docx` file from the documents page.
4. Log in as Carol: the roadmap is read-only. Log in as Bob: he can edit but cannot share or delete.
5. As Alice, open `/admin`: dashboard, the roadmap's "People with access" tab, a bulk action.

## Tests

```bash
php artisan test
```

| File                                     | Covers                                                                                                                                                                                                           |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `tests/Feature/DocumentSharingTest.php`  | Strangers get 403, viewers cannot write, editors cannot re-share, owners can share, content round-trips, viewers can copy                                                                                        |
| `tests/Feature/DocumentSecurityTest.php` | HTML sanitizing (scripts, event handlers, `javascript:` links), validation, re-sharing updates instead of duplicating, cross-document revoke returns 404, only owners delete, rate limiting, branded error pages |
| `tests/Feature/AdminPanelTest.php`       | Panel gating, admin powers limited to the panel, every admin page renders                                                                                                                                        |

## Project layout

```
app/
  Actions/SanitizeDocumentHtml.php        allowlist sanitizer, applied on every write
  Enums/ShareRole.php                     viewer | editor (labels, colours, icons)
  Http/Controllers/Documents/             DocumentController, DocumentShareController
  Http/Requests/Documents/                validation with human error messages
  Models/Document.php, DocumentShare.php
  Policies/DocumentPolicy.php             view / update / share / delete
  Filament/Resources/                     admin resources (Schemas/, Tables/, Pages/)
  Filament/Widgets/                       dashboard stats and charts
resources/js/
  pages/Welcome.vue, ErrorPage.vue
  pages/documents/Index.vue, Show.vue
  components/documents/                   editor, toolbar, share dialog, import, export, cards
  lib/documentImport.ts                   .txt / .md / .docx to HTML, in the browser
  lib/documentExport.ts                   Markdown, HTML and print export
database/seeders/DatabaseSeeder.php       demo accounts, documents and shares
```

The other notes: [ARCHITECTURE.md](ARCHITECTURE.md) (decisions and tradeoffs), [AI_WORKFLOW.md](AI_WORKFLOW.md) (how AI was used and checked), [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) (UI rules), [SUBMISSION.md](SUBMISSION.md) (what is included).

## Deploying

Any host with PHP 8.4+ and the `intl`, `pdo_sqlite` and `zip` extensions works.

```bash
git clone https://github.com/razapoonja/Ajaia-Docs.git && cd Ajaia-Docs
composer install --no-dev --optimize-autoloader     # also publishes Filament assets
cp .env.example .env
# set APP_ENV=production, APP_DEBUG=false, APP_URL=https://your-domain
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force --seed                  # first deploy only
npm ci && npm run build                             # or build locally, see below
php artisan optimize
```

Point the document root at `public/`, and make `storage/`, `bootstrap/cache/` and `database/` writable.

**Shared hosting without Node** (how the live app runs, on cPanel): run `npm run build` locally and upload the whole `public/build` folder, including `manifest.json`. Never upload `public/hot`. It only exists while `npm run dev` is running, and if it reaches the server every page loads its scripts from your local dev server and renders blank.

Production blocks destructive commands such as `migrate:fresh` (see `AppServiceProvider`). To reset the demo data on a live server, delete `database/database.sqlite`, then repeat the `touch` and `migrate --force --seed` steps. The app trusts proxy headers, so it generates `https` URLs correctly behind a load balancer or platform proxy.
