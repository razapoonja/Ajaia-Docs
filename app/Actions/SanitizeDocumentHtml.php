<?php

namespace App\Actions;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Reduces document HTML to the nodes the editor can produce.
 *
 * Anything else is either unwrapped (its text survives, e.g. <span>, <div>,
 * tables from .docx imports) or dropped with its contents (<script>, <style>).
 * Only the attributes the editor needs survive: link targets restricted to
 * http, https and mailto, and the data attributes that mark task lists.
 */
class SanitizeDocumentHtml
{
    /** @var array<string, array<int, string>> */
    private const ALLOWED = [
        'p' => [], 'br' => [], 'h1' => [], 'h2' => [], 'h3' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'code' => [], 'pre' => [], 'blockquote' => [], 'hr' => [],
        'ol' => [], 'ul' => ['data-type'], 'li' => ['data-type', 'data-checked'],
        'a' => ['href'],
    ];

    private const UNWRAPPED = [
        'div', 'span', 'label', 'section', 'article', 'header', 'footer', 'main', 'font', 'small', 'mark',
        'h4', 'h5', 'h6', 'del', 'ins', 'strike', 'sub', 'sup',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'figure', 'figcaption',
    ];

    private readonly HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->withMaxInputLength(2_000_000)
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer nofollow')
            ->forceAttribute('a', 'target', '_blank');

        foreach (self::ALLOWED as $element => $attributes) {
            $config = $config->allowElement($element, $attributes);
        }

        foreach (self::UNWRAPPED as $element) {
            $config = $config->blockElement($element);
        }

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function __invoke(?string $html): ?string
    {
        return $html === null ? null : $this->sanitizer->sanitize($html);
    }
}
