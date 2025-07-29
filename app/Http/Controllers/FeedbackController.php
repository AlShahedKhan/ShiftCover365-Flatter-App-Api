<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Http\Requests\FeedbackRequest;
use App\Jobs\Feedback\SendFeedbackEmail;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    /**
     * Submit feedback form
     */
    public function submit(FeedbackRequest $request)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return ResponseHelper::error('User not authenticated', 401);
            }

            $feedbackData = $request->validated();
            $feedbackData['user_id'] = Auth::id();

            Log::info('Creating feedback', [
                'user_id' => $feedbackData['user_id'],
                'feedback_data' => $feedbackData
            ]);

            $feedback = Feedback::create($feedbackData);

            Log::info('Feedback created successfully', [
                'feedback_id' => $feedback->id
            ]);

            // Dispatch email job to queue
            SendFeedbackEmail::dispatch($feedback);

            Log::info('Email job dispatched', [
                'feedback_id' => $feedback->id
            ]);

            return ResponseHelper::success([
                'feedback' => $feedback
            ], 'Feedback submitted successfully. Thank you for your input!', 201);

        } catch (\Exception $e) {
            Log::error('Feedback submission error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->validated()
            ]);

            return ResponseHelper::error('Failed to submit feedback. Please try again.', 500);
        }
    }
}
