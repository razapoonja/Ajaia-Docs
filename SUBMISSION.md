# Submission

**Candidate:** Raza Ali Poonja
**Role:** Full Stack Product Engineer, Ajaia
**Time spent:** about 4 hours

## Links

|                   |                                                                       |
| ----------------- | --------------------------------------------------------------------- |
| Live app          | https://ajaia-docs.razapoonja.com                                     |
| Admin panel       | https://ajaia-docs.razapoonja.com/admin (log in as alice@example.com) |
| Source            | https://github.com/razapoonja/Ajaia-Docs                              |
| Walkthrough video | see `VIDEO.txt`                                                       |

## Test accounts

Every account uses the password `password`.

| Email             | What to look at                                                                                                                    |
| ----------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| alice@example.com | Owner and admin. Eight documents, including "Q4 product roadmap" shared with Bob and Carol, and three shared with her. Start here. |
| bob@example.com   | Editor on the roadmap: toolbar present, no Share or Delete.                                                                        |
| carol@example.com | Viewer on the roadmap: read-only banner, no toolbar.                                                                               |

## Review it in five minutes

1. Log in as Alice and open "Q4 product roadmap". Edit it, watch the save state, reload.
2. Open **Share** and change Carol's access, then change it back.
3. Import a `.md` or `.docx` file from the documents page.
4. Log in as Carol (read-only), then Bob (can edit, cannot share or delete).
5. As Alice, open `/admin`: dashboard, the roadmap's "People with access" tab, a bulk action.

## What is in this folder

| File               | Purpose                                                                      |
| ------------------ | ---------------------------------------------------------------------------- |
| Source code        | The Laravel app (see README for the layout)                                  |
| `README.md`        | Local setup, test accounts, tests, deployment                                |
| `ARCHITECTURE.md`  | Priorities, decisions and tradeoffs, query shape, security, what was cut     |
| `AI_WORKFLOW.md`   | AI tools used, where they helped, what I changed or rejected, how I verified |
| `DESIGN_SYSTEM.md` | Colours, type, spacing and interaction rules the UI follows                  |
| `VIDEO.txt`        | Walkthrough video link                                                       |

## What works

- **Documents:** create, rename inline, edit, copy, delete. Autosave with visible state, Ctrl/Cmd+S, a warning before leaving with unsaved changes. Word count and reading time.
- **Editor:** headings 1–3, bold, italic, underline, strikethrough, inline code, links, bullet, numbered and checklist lists, quotes, code blocks, dividers. Bubble menu on selection, keyboard and Markdown-style shortcuts.
- **Import:** `.txt`, `.md` and `.docx` become a new document. Other types are rejected with a clear message.
- **Export:** Markdown, HTML, or print to PDF.
- **Sharing:** share by email as viewer or editor, change the role inline, revoke. Separate owned and shared lists with role badges, counts and search.
- **Access control:** server-side policy on every document route, covered by feature tests. Admin powers apply only inside the admin panel.
- **Persistence:** SQLite. Content and formatting survive refreshes; HTML is sanitized on every write.
- **Admin panel (Filament 5):** dashboard with stats and charts; documents, users and shares with view pages, relation managers, filters, tabs, grouping, global search and bulk actions.
- **Quality:** 44 feature tests, branded error pages, rate-limited sharing, responsive from 375 px, light and dark themes.

## Incomplete or out of scope

- No real-time collaboration: two people editing at once is last-write-wins.
- No comments or version history.
- No public link sharing: sharing is between registered users.

## With another 2–4 hours

1. Version history with restore.
2. Optimistic concurrency on save, rejecting stale writes.
3. Presence indicators with Laravel Reverb.
