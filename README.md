# Ajaia Docs

A lightweight collaborative document editor built for the Ajaia Full Stack Product Engineer assessment. Create and edit rich-text documents, import existing files, and share documents with other users as viewers or editors.

Live demo: _see SUBMISSION.md_

## What it does

- Create, rename, edit and delete documents
- Rich text: bold, italic, underline, three heading levels, bullet and numbered lists, undo/redo
- Autosave 800 ms after the last keystroke, with a visible save state
- Import a `.txt`, `.md` or `.docx` file as a new document (5 MB limit; other types are rejected with a clear message)
- Share a document with another registered user as **Can view** or **Can edit**; revoke at any time
- Document list split into "Your documents" and "Shared with you", with role badges
- Admin panel at `/admin` (Filament 5): dashboard with stats, activity and access charts; documents, users and shares with view pages, relation managers, filters, tabs, grouping, global search (Ctrl/Cmd+K) and bulk actions (transfer ownership, change access, grant/revoke admin, delete)
- Every document route is guarded server-side by `DocumentPolicy`

## Stack

Laravel 13 · Inertia 3 · Vue 3 + TypeScript · Tailwind 4 · shadcn-vue · Tiptap 3 · Filament 5 · SQLite · Pest

## Run it locally

Requirements: PHP 8.3+, Composer, Node 20+.

```bash
git clone <repo-url> ajaia-docs
cd ajaia-docs
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # Windows: type nul > database\database.sqlite
php artisan migrate:fresh --seed
php artisan wayfinder:generate --with-form   # typed route helpers (gitignored)
npm run build
php artisan serve
```

Open http://localhost:8000. For hot reload during development run `npm run dev` in a second terminal instead of `npm run build`.

## Test accounts

All seeded accounts use the password `password`.

| Email | Role in the seed data |
|---|---|
| alice@example.com | Owns "Q4 product roadmap" (shared with Bob and Carol) and "Private notes". Admin: can open `/admin`. |
| bob@example.com | Can **edit** the roadmap. Owns "Meeting notes". |
| carol@example.com | Can **view** the roadmap only. |

Eight more background accounts and twenty documents spread over the last 30 days are seeded so the admin dashboard has real history. They never share with Alice, Bob or Carol.

Suggested walkthrough: log in as Alice, open the roadmap, share it; log in as Carol and confirm the editor is read-only; log in as Bob and confirm Bob can edit but cannot share or delete.

## Tests

```bash
php artisan test
```

`tests/Feature/DocumentSharingTest.php` covers the access rules: strangers get 403, viewers cannot write, editors cannot re-share, owners can share, content survives a round trip. `tests/Feature/AdminPanelTest.php` covers admin gating.

## Project layout

```
app/
  Enums/ShareRole.php                     viewer | editor
  Http/Controllers/Documents/             DocumentController, DocumentShareController
  Http/Requests/Documents/                validation + human error messages
  Models/Document.php, DocumentShare.php
  Policies/DocumentPolicy.php             view / update / share / delete
  Filament/Resources/                     admin panel
resources/js/
  pages/documents/Index.vue, Show.vue
  components/documents/                   DocumentEditor, EditorToolbar, ShareDialog, ImportDocumentButton
  lib/documentImport.ts                   .txt / .md / .docx → HTML, in the browser
database/seeders/DatabaseSeeder.php       demo accounts and documents
DESIGN_SYSTEM.md                          tokens, type, spacing, interaction rules
ARCHITECTURE.md                           decisions and tradeoffs
AI_WORKFLOW.md                            how AI tools were used
SUBMISSION.md                             what is included, what is not
```

## Deploying

Any host that runs PHP 8.3 works. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`, keep `DB_CONNECTION=sqlite` (the file lives in `database/`, which must be writable), then `composer install --no-dev --optimize-autoloader`, `php artisan wayfinder:generate --with-form`, `npm run build`, `php artisan migrate --force --seed`, `php artisan optimize`.
