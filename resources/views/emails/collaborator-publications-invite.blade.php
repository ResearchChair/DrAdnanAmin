<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Co-author Publication Access</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <p>Hello,</p>
    <p>
        You were added as a co-author collaborator for this publication:
        <strong>{{ $publication->title }}</strong>.
    </p>
    <p>
        Use this secure link to view your co-authored publication list:
        <br>
        <a href="{{ $accessUrl }}">{{ $accessUrl }}</a>
    </p>
    <p>If you were not expecting this email, you can ignore it.</p>
</body>
</html>
