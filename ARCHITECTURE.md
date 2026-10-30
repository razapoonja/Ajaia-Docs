# Architecture note

## In one paragraph

A Laravel monolith with an Inertia and Vue front end. A document is a row with an HTML `content` column produced by Tiptap. Sharing is a `document_shares` table with a `role` column. Every read or write of a document goes through `DocumentPolicy`, the one place the access rules live, and every write of content goes through an HTML sanitizer. Files are parsed in the browser, so the server only ever receives text. SQLite is the store: the brief allows it, and it removes a whole class of setup problems for reviewers.

## Why Laravel for this MVP, and what changes in production

This is a four-hour MVP, so I picked the stack that gets a correct, secure, full-stack product live fastest. Laravel gives authentication, validation, policies, migrations and queues out of the box. Inertia removes the API layer. Filament produced a full admin panel (dashboard, relation managers, bulk actions) in a fraction of the time a hand-built one would take. Shared hosting with SQLite was enough to put a working build in front of reviewers.

A real product would get the stack and hosting its requirements call for, not the ones that were fastest for a demo:

- **Traffic.** Laravel's default PHP-FPM model boots the framework on every request and isn't built for long-lived connections. At high traffic I would run it under Laravel Octane (FrankenPHP or Swoole) behind a load balancer and scale horizontally. Or, if the product leaned towards real-time and high concurrency, I'd move those paths to a stack built for it: Node.js or Go, or FastAPI for Python-heavy AI features.
- **Real-time collaboration.** A dedicated WebSocket service holding Y.js (CRDT) documents, with Redis pub/sub between instances. The REST app would handle accounts, sharing and persistence.
- **Hosting.** Containers on Kubernetes (EKS) or a managed container platform, with autoscaling and health and liveness probes. Postgres on RDS with backups and read replicas. Redis for cache, sessions and queues. S3 for imports and exports. CloudFront in front of the static assets.
- **Background work.** Queues (SQS or RabbitMQ) for imports, exports, notification emails and search indexing, so requests stay fast.
- **Operations.** Prometheus and Grafana for metrics and alerts, Sentry for errors, structured logs, and a CI/CD pipeline that runs tests, static analysis and dependency and container scanning before every deploy. Secrets in a secrets manager, never in `.env` files on servers.
- **Security at scale.** SSO (SAML/OIDC) for teams, audit logs for sharing changes, per-tenant data isolation, and rate limits at the edge as well as in the app.

What carries over unchanged: the data model, the policy-based access rules, sanitizing on write, and the tests. Those decisions don't depend on the framework.

## What I prioritised, in order

1. **Correct access control.** It is the feature most likely to be wrong in a rushed build and the first one a reviewer will probe. Policy methods are small and tested, controllers call `Gate::authorize` on every action, and the front end only hides buttons; it never enforces anything.
2. **An editor that feels finished.** Visible autosave, inline title editing, a read-only mode that looks intentional rather than broken, a keyboard-reachable toolbar with `aria-pressed`, and no lost keystrokes when you navigate away.
3. **Reviewer time.** Seeded accounts with a pre-shared document, demo logins on the landing and login pages, an admin panel to inspect the data, and short setup.
4. **Everything else.** Import, export, copy, the admin dashboard, mobile layout, dark mode.

## Decisions and tradeoffs

**Inertia instead of a separate SPA and API.** No tokens, no CORS, no duplicated validation. Controllers return pages and forms post to routes. The cost: the app can't serve a native mobile client, which is out of scope.

**HTML in a `longText` column instead of Tiptap JSON.** HTML is what Tiptap emits and accepts, it is readable in the database and the admin panel, and both import formats (Markdown, docx) already convert to HTML. JSON becomes the right call once you need server-side transforms or real-time collaboration. Validation caps content at 2 MB.

**Editor scope.** Tiptap 3's starter kit plus its list and utility packages cover headings, marks, links, quotes, code, dividers and checklists with no extra dependencies. I left out text alignment, colours and tables: each needs inline styles or a much wider sanitizer allowlist, for little gain in a team docs tool.

**Roles as an enum column, not a permissions table.** Two roles cover the brief. `ShareRole` is a backed enum cast on the model, so adding a `commenter` role is one enum case and one policy line. The enum also gives the admin panel its labels, colours and icons.

**Sharing by email address.** It is what people expect from Google Docs, and it avoids a user picker that would list every account. Unknown emails and sharing with yourself are rejected with a message that says what to do. Sharing again with the same person updates the role (an upsert on a unique index) instead of creating a duplicate.

**Client-side file parsing.** `mammoth` (docx) and `marked` (md) run in the browser, and the result goes through the same validated `store` endpoint as "New document". No upload handling, no temp files, no MIME sniffing on the server, and the 5 MB check happens before any bytes leave the machine. `mammoth` is loaded lazily, so it stays out of the main bundle.

**Export and copy without server work.** Markdown conversion, a standalone HTML download and print-to-PDF (through a hidden iframe) all run in the browser, so there is no PDF library on the server. "Make a copy" is a small server action: anyone who can view a document can copy it into their own library, and the copy starts unshared. That gives a viewer a way to work on their own version without asking for edit rights.

**Debounced autosave over Inertia.** A save goes out 800 ms after the last change, as a partial reload, so the editor isn't re-mounted and only a few hundred bytes come back. Leaving for another page inside the app waits for a pending save first, and closing the tab with unsaved changes shows the browser's warning. Two people editing the same document at the same time is last-write-wins; real-time collaboration would need Y.js or similar and was the first thing cut.

**SQLite.** It persists on a normal server and keeps setup to a clone and an install. Nothing in the schema or queries is SQLite-specific, so moving to Postgres is a `.env` change.

**Filament for admin.** Laid out the way Filament 5 generates resources: each one has `Schemas/` (form and infolist), `Tables/` and `Pages/`, so no file mixes concerns. Documents and users have list, view and edit pages with relation managers (people with access; documents owned; documents shared with them). Shares is a modal resource with grouping by document, person or access. Bulk actions cover the real admin jobs: transfer ownership, change access, grant or revoke admin, delete. Access is gated by `User::canAccessPanel`. `DocumentPolicy::before` gives admins full rights only while the admin panel is serving the request (`Filament::isServing()`); in the app an admin follows the same sharing rules as everyone else. Admins cannot delete or demote themselves.

## Query shape

- **Document list:** two queries (owned with `withCount('shares')`, shared with `with('owner')`). It selects `substr(content, 1, 2000)` instead of the full body, because the cards only show a preview.
- **Document page:** one query for the document, with `owner` and `shares.user` eager-loaded before authorization, so the policy checks shares in memory.
- **Sidebar:** library counts and recent documents are a lazy shared prop (three small queries), so partial reloads such as autosave never run them.
- **Autosave:** page props are closures and each save asks only for `permissions`, so a save returns about 230 bytes.
- **Indexes:** a unique `(document_id, user_id)` index for sharing, and a second `(user_id, document_id)` index for "shared with me". SQLite does not index foreign keys on its own.
- **Admin:** every table eager-loads in `modifyQueryUsing` and counts with `withCount`, so no page runs per-row queries.
- **Guard rail:** in development, lazy loading is logged (`Model::preventLazyLoading` with a logging handler), so an N+1 shows up in the log without breaking the page.

## Security

- Every document route sits behind `auth` and `verified`, and authorization is a policy, never a hidden button.
- Route-model binding plus an explicit `document_id` check when revoking a share, so a share id from another document returns 404.
- Form requests validate every input; titles and content are length-capped. Mass assignment is limited by `#[Fillable]`.
- `SanitizeDocumentHtml` (Symfony HtmlSanitizer) reduces content to exactly the nodes the editor produces. It runs in a model mutator, so the app, the admin panel, import and the seeder all go through it. Scripts, styles, event handlers and inline styles are removed. Links are limited to `http`, `https` and `mailto` and always get `rel="noopener noreferrer nofollow"`. Unknown containers from `.docx` files are unwrapped so their text survives.
- Sharing is rate limited to 30 requests a minute per user. The "no account uses that email" message is helpful, but without a limit it could be used to list registered emails.
- 403, 404, 419 and 429 render a branded page that says what to do next. 500 and 503 do the same when debug mode is off. Admin and Livewire requests keep Filament's handling.
- CSRF is on, the app trusts proxy headers for correct `https` URLs, and no secrets are committed.

## What I cut, and why

| Cut                             | Reason                                                                                     |
| ------------------------------- | ------------------------------------------------------------------------------------------ |
| Real-time collaboration         | Needs a CRDT and a websocket server. The brief says not to rebuild Google Docs.            |
| Comments, version history       | Each is a few hours of work on its own; neither was required.                              |
| Public link sharing             | Needs a token model and a guest route. User-to-user sharing shows the access model better. |
| Text alignment, colours, tables | Need inline styles or a much wider sanitizer allowlist, for little value here.             |

## With another 2–4 hours

1. **Version history.** Snapshot the content on each explicit save and on a timer, with a restore button.
2. **Optimistic concurrency.** Send `updated_at` with each save and reject stale writes with a "someone else edited this" message. Cheap insurance before real-time editing.
3. **Presence.** Laravel Reverb to show who else has a document open, as the first step towards collaboration.
