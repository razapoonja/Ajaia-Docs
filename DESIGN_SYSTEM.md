# Design system

Ajaia Docs is a writing tool. The interface should disappear behind the document. Every decision below follows from that.

## Direction

Quiet minimalism. Slate neutrals, one blue accent, generous whitespace, no decoration. The document body is the only thing on screen that gets to be large.

## Tokens

All colours are semantic CSS variables defined in `resources/css/app.css` and consumed through Tailwind utilities (`bg-background`, `text-muted-foreground`, `bg-primary`). Components never use raw hex.

| Role | Light | Dark | Use |
|---|---|---|---|
| background | slate-50 `#F8FAFC` | `#0B1120` | page |
| card | white | `#111827` | panels, editor surface |
| foreground | slate-900 `#0F172A` | slate-50 | body text |
| muted-foreground | slate-500 `#64748B` | slate-400 | secondary text, timestamps |
| primary | blue-600 `#2563EB` | blue-500 | the one CTA per screen, active states |
| border | slate-200 `#E2E8F0` | slate-800 | dividers, inputs |
| destructive | red-600 `#DC2626` | red-500 | delete, revoke access |

Contrast: all text pairs meet WCAG AA (4.5:1). Primary on white is 5.2:1.

## Typography

Instrument Sans everywhere (already shipped by the starter kit, no extra font request).

| Token | Size / line | Weight | Use |
|---|---|---|---|
| display | 30 / 36 | 600 | page title |
| heading | 20 / 28 | 600 | card titles, dialog titles |
| body | 16 / 24 | 400 | UI text, lists |
| small | 14 / 20 | 400–500 | metadata, labels |
| caption | 12 / 16 | 500 | badges, helper text |

Editor body: 17px, line-height 1.7, max-width 72ch, centred. Headings inside the document follow a 1.25 scale.

## Spacing and shape

4px base. Use 4 / 8 / 12 / 16 / 24 / 32 / 48. Page gutter 16px on mobile, 32px from 768px. Radius 8px for inputs and buttons, 12px for cards. Shadows: none, except dialogs (`shadow-lg`). Elevation is expressed by border, not shadow.

## Layout

- Sidebar navigation on desktop (starter kit shell), collapses to an icon rail and a sheet on mobile.
- Document list: single column of rows, grouped "Your documents" and "Shared with you". Rows show title, owner or role badge, last edited. One primary CTA: New document.
- Editor: toolbar pinned to the top of the editor surface, document body below. Title is an inline editable heading, not a form field. Save state shown as quiet text ("Saved · just now"), never a modal.

## Components

Use the shadcn-vue components in `resources/js/components/ui`. Do not write a new button, dialog, or input. Icons are Lucide, 16px in buttons and 20px standalone, stroke 2. No emoji as icons.

## Interaction

- Every clickable element has `cursor-pointer`, a visible focus ring, and a hover state that transitions in 150ms.
- Buttons disable and show a spinner while a request is in flight.
- Destructive actions (delete document, revoke share) confirm first and use `variant="destructive"`.
- Autosave debounces 800ms after the last keystroke. Save status is announced in an `aria-live="polite"` region.
- Errors appear next to the field that caused them, in red text with an icon.
- Toasts (vue-sonner) for success feedback only; they auto-dismiss in 4s.
- Respect `prefers-reduced-motion`: transitions drop to 0ms.

## Accessibility checklist

- Keyboard reachable toolbar; each formatting button has `aria-label` and `aria-pressed`.
- Role badges carry text ("Can edit"), not colour alone.
- Minimum touch target 40×40px on mobile.
- Headings in page content are sequential (one h1 per page).

## Writing

Sentence case for headings and buttons. Short labels: "New document", "Share", "Can edit". No exclamation marks. Error messages name the cause and the fix: "That file is 12 MB. The limit is 5 MB."
