<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>New Feedback Submitted - ShiftCover365</title>
</head>
<body>
    <h1>New Feedback Submitted - ShiftCover365</h1>

    <div style="background-color: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h2>User Information</h2>
        <p><strong>Name:</strong> {{ $user_name }}</p>
        <p><strong>Email:</strong> {{ $user_email }}</p>
        <p><strong>User Type:</strong> {{ $user_type }}
            @if($other_user_type)
                ({{ $other_user_type }})
            @endif
        </p>
    </div>

    <div style="background-color: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h2>Feedback Details</h2>
        <p><strong>Overall Rating:</strong>
            @for($i = 1; $i <= 5; $i++)
                @if($i <= $overall_rating)
                    ⭐
                @else
                    ☆
                @endif
            @endfor
            ({{ $overall_rating }}/5)
        </p>
        <p><strong>Feature Used:</strong> {{ $feature_used }}
            @if($other_feature)
                ({{ $other_feature }})
            @endif
        </p>
    </div>

    @if($suggestions)
    <div style="background-color: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h2>Suggestions/Comments</h2>
        <div style="background-color: white; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;">
            {{ $suggestions }}
        </div>
    </div>
    @endif

    <div style="margin-top: 30px; padding: 15px; background-color: #e9ecef; border-radius: 5px;">
        <p><small>Submitted on: {{ now()->format('F j, Y \a\t g:i A') }}</small></p>
    </div>
</body>
</html>
