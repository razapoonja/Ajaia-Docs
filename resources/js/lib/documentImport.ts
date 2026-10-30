import { marked } from 'marked';

export const SUPPORTED_EXTENSIONS = ['txt', 'md', 'docx'] as const;
export const MAX_IMPORT_BYTES = 5 * 1024 * 1024;

export type ImportedDocument = { title: string; content: string };

function extensionOf(file: File): string {
    return file.name.split('.').pop()?.toLowerCase() ?? '';
}

function titleFrom(file: File): string {
    return file.name.replace(/\.[^.]+$/, '').trim() || 'Imported document';
}

function escapeHtml(text: string): string {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function plainTextToHtml(text: string): string {
    return text
        .split(/\n{2,}/)
        .map((block) => block.trim())
        .filter(Boolean)
        .map((block) => `<p>${escapeHtml(block).replace(/\n/g, '<br>')}</p>`)
        .join('');
}

/**
 * Turns a .txt, .md or .docx file into editor-ready HTML.
 * Parsing happens in the browser so the server only ever receives text.
 */
export async function importDocumentFile(
    file: File,
): Promise<ImportedDocument> {
    const extension = extensionOf(file);

    if (
        !SUPPORTED_EXTENSIONS.includes(
            extension as (typeof SUPPORTED_EXTENSIONS)[number],
        )
    ) {
        throw new Error(
            `"${file.name}" isn't supported. Upload a .txt, .md or .docx file.`,
        );
    }

    if (file.size > MAX_IMPORT_BYTES) {
        const mb = (file.size / 1024 / 1024).toFixed(1);
        throw new Error(`That file is ${mb} MB. The limit is 5 MB.`);
    }

    const title = titleFrom(file);

    if (extension === 'docx') {
        const mammoth = await import('mammoth');
        const { value } = await mammoth.convertToHtml({
            arrayBuffer: await file.arrayBuffer(),
        });

        return { title, content: value };
    }

    const text = await file.text();

    if (extension === 'md') {
        return { title, content: await marked.parse(text) };
    }

    return { title, content: plainTextToHtml(text) };
}
