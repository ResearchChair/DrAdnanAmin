<?php

namespace App\Services\Applications;

use App\Models\ApplicationDraft;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ApplicationPdfService
{
    public function download(ApplicationDraft $draft)
    {
        $html = view('applications.draft-pdf', [
            'draft' => $draft,
            'bodyHtml' => $this->markdownToHtml($draft->output_markdown),
        ])->render();

        $filename = $draft->downloadBasename().'.pdf';

        return Pdf::loadHTML($html)
            ->setPaper('a4')
            ->download($filename);
    }

    public function markdownToHtml(string $markdown): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $markdown);
        $escaped = e($text);

        // Headings
        $escaped = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $escaped) ?? $escaped;
        $escaped = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $escaped) ?? $escaped;
        $escaped = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $escaped) ?? $escaped;

        // Bold / italic
        $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $escaped) ?? $escaped;

        // Unordered lists
        $escaped = preg_replace('/^[-*] (.+)$/m', '<li>$1</li>', $escaped) ?? $escaped;
        $escaped = preg_replace('/(?:<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $escaped) ?? $escaped;

        // Ordered lists (simple)
        $escaped = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $escaped) ?? $escaped;

        $parts = preg_split("/\n{2,}/", $escaped) ?: [];
        $html = '';
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (Str::startsWith($part, ['<h1', '<h2', '<h3', '<ul', '<ol'])) {
                $html .= $part;
            } else {
                $html .= '<p>'.nl2br($part).'</p>';
            }
        }

        return $html !== '' ? $html : '<p>'.nl2br(e($markdown)).'</p>';
    }
}
