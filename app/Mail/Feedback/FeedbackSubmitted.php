<?php

namespace App\Mail\Feedback;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class FeedbackSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    protected Feedback $feedback;

    /**
     * Create a new message instance.
     */
    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Feedback Submitted - ShiftCover365',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback.submitted',
            with: [
                'user_type' => $this->feedback->user_type,
                'overall_rating' => $this->feedback->overall_rating,
                'feature_used' => $this->feedback->feature_used,
                'suggestions' => $this->feedback->suggestions,
                'other_user_type' => $this->feedback->other_user_type,
                'other_feature' => $this->feedback->other_feature,
                'user_name' => $this->feedback->user->name,
                'user_email' => $this->feedback->user->email,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
