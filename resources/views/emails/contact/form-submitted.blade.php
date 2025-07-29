<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contact Form Submission</title>
</head>
<body>
    <h1>New Contact Form Submission</h1>
    <p><strong>Subject:</strong> {{ $subject }}</p>
    <p><strong>Message:</strong></p>
    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0;">
        {{ $message_text }}
    </div>
    <p><small>Submitted on: {{ now()->format('F j, Y \a\t g:i A') }}</small></p>
</body>
</html>
