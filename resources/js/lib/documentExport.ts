function slugify(title: string): string {
    return title.trim().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-').toLowerCase() || 'document';
}

function download(filename: string, content: string, type: string): void {
    const url = URL.createObjectURL(new Blob([content], { type }));
    const link = Object.assign(document.createElement('a'), { href: url, download: filename });
    link.click();
    URL.revokeObjectURL(url);
}

function inlineToMarkdown(node: Node): string {
    if (node.nodeType === Node.TEXT_NODE) {
        return (node.textContent ?? '').replace(/([*_`\\])/g, '\\$1');
    }

    if (!(node instanceof HTMLElement)) {
        return '';
    }

    const inner = Array.from(node.childNodes).map(inlineToMarkdown).join('');

    switch (node.tagName) {
        case 'STRONG':
        case 'B':
            return inner ? `**${inner}**` : '';
        case 'EM':
        case 'I':
            return inner ? `*${inner}*` : '';
        case 'U':
            return inner ? `<u>${inner}</u>` : '';
        case 'S':
        case 'DEL':
            return inner ? `~~${inner}~~` : '';
        case 'CODE':
            return `\`${node.textContent ?? ''}\``;
        case 'A':
            return `[${inner}](${node.getAttribute('href') ?? ''})`;
        case 'BR':
            return '  \n';
        default:
            return inner;
    }
}

function listToMarkdown(list: HTMLElement, depth: number): string {
    const ordered = list.tagName === 'OL';
    const isTaskList = list.dataset.type === 'taskList';

    return Array.from(list.children)
        .map((item, index) => {
            const checkbox = (item as HTMLElement).dataset.checked === 'true' ? '[x] ' : '[ ] ';
            const marker = ordered ? `${index + 1}.` : isTaskList ? `- ${checkbox.trimEnd()}` : '-';
            const indent = '  '.repeat(depth);
            const text = Array.from(item.childNodes)
                .filter((child) => !(child instanceof HTMLElement && ['UL', 'OL'].includes(child.tagName)))
                .map(inlineToMarkdown)
                .join('')
                .trim();
            const nested = Array.from(item.children)
                .filter((child) => ['UL', 'OL'].includes(child.tagName))
                .map((child) => listToMarkdown(child as HTMLElement, depth + 1))
                .join('\n');

            return `${indent}${marker} ${text}${nested ? `\n${nested}` : ''}`;
        })
        .join('\n');
}

/**
 * Converts the editor's HTML (headings, paragraphs, lists, bold, italic,
 * underline) to Markdown. Underline has no Markdown syntax, so it stays as <u>.
 */
export function htmlToMarkdown(html: string): string {
    const body = new DOMParser().parseFromString(html, 'text/html').body;

    return Array.from(body.children)
        .map((block) => {
            const text = inlineToMarkdown(block).trim();

            switch (block.tagName) {
                case 'H1':
                    return `# ${text}`;
                case 'H2':
                    return `## ${text}`;
                case 'H3':
                    return `### ${text}`;
                case 'UL':
                case 'OL':
                    return listToMarkdown(block as HTMLElement, 0);
                case 'BLOCKQUOTE':
                    return text.split('\n').map((line) => `> ${line}`).join('\n');
                case 'PRE':
                    return `\`\`\`\n${block.textContent ?? ''}\n\`\`\``;
                case 'HR':
                    return '---';
                default:
                    return text;
            }
        })
        .filter(Boolean)
        .join('\n\n')
        .concat('\n');
}

function escapeHtml(text: string): string {
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function standaloneHtml(title: string, html: string): string {
    return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>${escapeHtml(title)}</title>
<style>
  body { font: 16px/1.7 system-ui, -apple-system, 'Segoe UI', sans-serif; color: #0f172a; max-width: 72ch; margin: 3rem auto; padding: 0 1.5rem; }
  h1 { font-size: 2rem; line-height: 1.2; } h2 { font-size: 1.5rem; } h3 { font-size: 1.25rem; }
  ul, ol { padding-left: 1.5rem; } li > p { margin: 0; }
  ul[data-type=taskList] { list-style: none; padding-left: 0.25rem; }
  blockquote { border-left: 3px solid #e2e8f0; margin-left: 0; padding-left: 1rem; color: #475569; }
  code { font-family: ui-monospace, Menlo, Consolas, monospace; background: #f1f5f9; padding: 0.1em 0.3em; border-radius: 4px; }
  pre { background: #f1f5f9; padding: 0.9rem 1rem; border-radius: 8px; overflow-x: auto; } pre code { background: none; padding: 0; }
  hr { border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0; } a { color: #2563eb; }
  @page { margin: 2cm; }
</style>
</head>
<body>${html}</body>
</html>`;
}

export function exportMarkdown(title: string, html: string): void {
    download(`${slugify(title)}.md`, htmlToMarkdown(html), 'text/markdown;charset=utf-8');
}

export function exportHtml(title: string, html: string): void {
    download(`${slugify(title)}.html`, standaloneHtml(title, html), 'text/html;charset=utf-8');
}

/**
 * Opens a clean print view; the browser's print dialog offers "Save as PDF".
 * The HTML has already been sanitized server-side and by the editor schema.
 */
export function printDocument(title: string, html: string): void {
    const frame = Object.assign(document.createElement('iframe'), { title: 'Print preview' });
    frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0';
    document.body.appendChild(frame);

    const doc = frame.contentDocument;
    if (!doc || !frame.contentWindow) {
        frame.remove();
        return;
    }

    doc.open();
    doc.write(standaloneHtml(title, html));
    doc.close();

    frame.contentWindow.focus();
    frame.contentWindow.print();
    setTimeout(() => frame.remove(), 1000);
}
