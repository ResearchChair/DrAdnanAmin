<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $draft->typeLabel() }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.45; color: #111; margin: 28px 36px; }
        h1 { font-size: 16pt; margin: 0 0 8px; }
        h2 { font-size: 13pt; margin: 18px 0 6px; }
        h3 { font-size: 12pt; margin: 14px 0 6px; }
        p { margin: 0 0 10px; }
        ul, ol { margin: 0 0 10px 18px; padding: 0; }
        li { margin-bottom: 4px; }
        .meta { font-size: 9pt; color: #555; margin-bottom: 18px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .footer { margin-top: 24px; font-size: 8pt; color: #777; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ $draft->typeLabel() }}</h1>
    <div class="meta">
        @if($draft->position_title)
            <div><strong>Position:</strong> {{ $draft->position_title }}</div>
        @endif
        @if($draft->institution)
            <div><strong>Institution:</strong> {{ $draft->institution }}</div>
        @endif
        <div><strong>Generated:</strong> {{ $draft->updated_at?->format('Y-m-d H:i') }}</div>
    </div>

    {!! $bodyHtml !!}

    <div class="footer">
        AI-assisted draft from academic portfolio data. Verify all facts before submission.
    </div>
</body>
</html>
