# AI workflow note

## Tools

- **Claude** (claude.ai, linked to this repository and to my Chrome browser). Used for planning, first-pass code, browser testing of the running app, debugging the deployment, and drafting these notes.
- **Laravel Boost**, with its guidelines and skills installed in the repo, so generated code targets the exact installed versions: Laravel 13, Inertia 3, Filament 5, Tiptap 3, Pest 5.
- **Wayfinder** for typed route helpers, which removed a whole class of "the AI guessed the URL" bugs.

I read every change before committing it, and ran the tests, the type check and the build after each round.

## Where AI materially sped things up

**Scaffolding.** Migrations, models and relationships, the policy, form requests, both controllers, the seeder and the first Pest tests came out in one pass of about ten minutes, against roughly forty-five by hand. The shape matched what I would have written; the saving was typing.

**An unfamiliar component library.** The starter kit ships shadcn-vue components I had not used. Claude read their source in the repo and built the share dialog, toolbar, editor wrapper and list pages on those primitives without guesswork.

**Version-specific APIs.** Filament 5 and Inertia 3 are recent. Before writing code against them, Claude checked the installed vendor source and the Filament 5.x docs, so the admin panel follows the generated-resource layout and uses methods that actually exist.

**Testing the running app.** Claude drove my Chrome through real flows: create, format, rename, reload, share, log in as each person, import, copy, switch documents, check phone and tablet widths, and dark mode. Several of the bugs below were found this way, not by unit tests.

**Docs.** The README and this note started as drafts from decisions I had already made, then I edited the reasoning where it didn't match why I chose something.

## What I changed or rejected

- **Stack.** The first suggestion was Next.js, Neon and Vercel. I chose Laravel: I deploy it without friction, and policies, form requests and the starter kit's auth map directly onto the brief.
- **Admin panel.** The AI recommended skipping it as out of scope. I disagreed, because a Filament panel is cheap and gives reviewers a direct view of the sharing data. I had it built after the core worked, not before.
- **Storage format.** The first draft stored Tiptap JSON. I switched to HTML so Markdown and docx imports feed the same column, and so the admin panel can show content readably.
- **Share picker.** An early draft used a dropdown of all users. Replaced with an email field so the app doesn't list every account.
- **Landing page, first version.** Rejected as a generic SaaS template: grid backdrop, glow blobs, a gradient headline word, rows of identical cards. Rebuilt around the product: the hero is a document page, with the toolbar and margin notes showing who has access.
- **A "Carol is viewing" badge** in the landing preview. Removed: the app has no live presence, and it would advertise a feature reviewers can't find.
- **Filament resources.** The first version used flat single-file resources. Restructured to Filament 5's convention (`Schemas/`, `Tables/`, `Pages/`) after reading the 5.x docs.
- **Starter-kit details.** A breadcrumb used a layout signature the kit doesn't support, and route generation without `--with-form` broke type checking in the auth pages. Both fixed.

## Bugs caught by verification, not by the AI

- **Wrong document saved.** Moving straight from one document to another (sidebar, "Make a copy") reused the editor page with the previous document's save URL. Testing the copy flow by hand produced a 403, because the new copy tried to save into the original. The page now resets for each document, and in-app navigation finishes a pending save before leaving.
- **Admin rights leaked into the app.** The admin override let an admin edit documents shared with them as view-only. It is now limited to admin panel requests, with tests for both sides.
- **Chart crash.** This app uses immutable dates and the generated chart code type-hinted the mutable class. The admin smoke test, which renders every admin page, caught it.
- **Silent truncation.** Symfony's HTML sanitizer cuts input at 20,000 characters by default. Reading the library source before relying on it caught this; long documents would have been cut off without an error.
- **Wasteful autosave.** Every save re-downloaded the whole document. Checking response sizes in the browser exposed it; a partial reload brought each save down to about 230 bytes.
- **Deployment.** The starter kit blocks `migrate:fresh` in production, so the generated deploy steps would have failed. After the first upload the live site rendered blank: inspecting the served HTML showed it loading scripts from a local dev server, because the `public/hot` file had been uploaded and the build manifest had not. Both fixed on the server, and the README now warns about it.

## How I verified

- `php artisan test`: 44 feature tests covering the access rules (stranger 403, viewer can't write, editor can't re-share, cross-document revoke 404, only the owner deletes), sanitizing (scripts, event handlers, `javascript:` links), validation, rate limiting, copy, error pages, and an admin smoke test that renders every admin page.
- `npm run types:check` and `npm run build` clean after each round.
- Manual passes in Chrome as Alice, Bob and Carol, with a full reload after edits to prove persistence, at 375 px, 768 px and desktop widths.
- Reviewed queries for N+1: the documents list is two queries, a document page is one plus eager loads, and the admin tables eager-load.
- A production smoke test from the server after deploying: log in as Alice, load the documents list, a document and the admin panel.

## What I would not let AI decide

The scope cuts, the storage format, the permission model, and what to leave out of the landing page. Those are the product calls the brief is asking about, and they are mine.
