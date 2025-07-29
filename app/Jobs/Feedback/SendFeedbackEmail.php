<?php

namespace App\Jobs\Feedback;

use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\Feedback\FeedbackSubmitted;
use App\Models\Feedback;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendFeedbackEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $feedback;

    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    public function handle(): void
    {
        Mail::to('alshahed.cse@gmail.com')->queue(new FeedbackSubmitted(
            $this->feedback
        ));
    }
}
