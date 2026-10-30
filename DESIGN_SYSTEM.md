# Design system

Ajaia Docs is a writing tool, so the interface should step back and let the document lead. Every rule below follows from that.

## Direction

Quiet, paper-on-desk minimalism. Slate neutrals, one blue accent, generous whitespace, almost no decoration. The document is the only large thing on screen. The landing page uses the same idea literally: its hero is a document page on a muted desk, with the editor toolbar on top and margin notes showing who has access.

## Colour

All colours are semantic CSS variables in `resources/css/app.css`, used through Tailwind utilities (`bg-background`, `text-muted-foreground`, `bg-primary`). Components never use raw hex values.

| Token            | Light     | Dark       | Used for                                             |
| ---------------- | --------- | ---------- | ---------------------------------------------------- |
| background       | slate-50  | deep navy  | app background                                       |
| muted            | slate-100 | slate-800  | the "desk" behind pages, quiet panels                |
| card             | white     | dark slate | document surface, cards, dialogs                     |
| foreground       | slate-900 | slate-50   | body text                                            |
| muted-foreground | slate-500 | slate-400  | metadata, timestamps, helper text                    |
| primary          | blue-600  | blue-500   | the one main action per screen, active states, links |
| border           | slate-200 | slate-800  | dividers, inputs                                     |
| destructive      | red-600   | red-500    | delete, revoke                                       |

People are told apart by colour in avatars only, always alongside initials and a role label: the owner in primary blue, Bob in emerald, Carol in violet. Light is the default theme; dark is available in settings. Text contrast meets WCAG AA in both.

## Typography

Instrument Sans throughout, with hierarchy from size and weight rather than a second typeface.

| Role       | Size                     | Weight  | Used for                         |
| ---------- | ------------------------ | ------- | -------------------------------- |
| Hero title | 42–72 px, tight tracking | 600     | landing page only                |
| Page title | 30 px                    | 600     | "Your documents", document title |
| Section    | 14 px                    | 500     | "Owned by me", "Shared with me"  |
| Body       | 15–16 px                 | 400     | UI text                          |
| Caption    | 12 px                    | 400–500 | badges, timestamps, save state   |

Document body: 17 px, line height 1.7, max 80 characters per line. Headings inside documents follow a 1.25 ratio.

## Spacing and shape

A 4 px base: 4, 8, 12, 16, 24, 32, 48, 64. Page gutters are 16 px on phones and 32–40 px from tablets up. Radius is 8 px for inputs and buttons, and 12 px for cards and the editor surface. Elevation comes from borders; shadows are reserved for dialogs, menus and the landing page's paper.

## Layout

- **App shell:** a sidebar with New document, Library (All, Owned by me, Shared with me, with counts), Recent documents, and an Admin link for admins. On phones it becomes a slide-out drawer.
- **Documents:** a responsive card grid (1 to 5 columns). Each card shows a miniature page with a text preview, the title, the time since the last edit, and a sharing badge. The first card creates a blank document.
- **Editor:** the title is editable inline (a pencil appears on hover), with the save state beside it. The toolbar sticks to the top of the editor and scrolls sideways on phones instead of wrapping. A footer shows word count, reading time and the Ctrl+S hint.
- **Error pages:** a single card on the desk background that says what happened and offers a way back.

## Components

Use the shadcn-vue components in `resources/js/components/ui`; don't hand-build a new button, dialog, select or menu. Icons are Lucide at 16 px in buttons and 20 px standalone. No emoji as icons.

## Interaction

- Every clickable element has a pointer cursor, a visible focus ring and a 150 ms hover transition.
- Buttons disable and change their label while a request is in flight ("Sharing…", "Creating…").
- Delete asks for confirmation first and uses the destructive style.
- Autosave runs 800 ms after the last keystroke, and its state is announced in an `aria-live` region.
- Errors appear next to the field that caused them. Toasts are for confirmations and dismiss themselves.
- Motion is limited to a blinking caret on the landing page, small hover lifts and menu transitions. `prefers-reduced-motion` turns transitions off.

## Accessibility

- The formatting toolbar is a labelled `toolbar`. Every button has an `aria-label`, its shortcut in `aria-keyshortcuts`, and `aria-pressed` for toggles.
- Role badges use text ("Can edit"), never colour alone.
- Touch targets are at least 32–40 px, and layouts are checked at 375 px, 768 px and desktop widths.
- One `h1` per page, with headings in order.

## Writing

Sentence case for headings and buttons. Short, plain labels: "New document", "Share", "Can edit", "Make a copy". No exclamation marks. Errors say what went wrong and how to fix it, for example: "That file is 12 MB. The limit is 5 MB."
