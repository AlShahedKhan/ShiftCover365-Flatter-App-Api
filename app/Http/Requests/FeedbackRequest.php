<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_type' => 'required|string|in:Manager,Locum Worker,Admin,Other',
            'overall_rating' => 'required|integer|min:1|max:5',
            'feature_used' => 'required|string|in:Shift Posting,Till Discrepancy Alerts,Digital Accountability Agreement,Shift Logs,Other',
            'suggestions' => 'nullable|string|max:1000',
            'other_user_type' => 'nullable|required_if:user_type,Other|string|max:255',
            'other_feature' => 'nullable|required_if:feature_used,Other|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_type.required' => 'Please select what best describes you.',
            'user_type.in' => 'Please select a valid user type.',
            'overall_rating.required' => 'Please provide your overall experience rating.',
            'overall_rating.min' => 'Rating must be at least 1 star.',
            'overall_rating.max' => 'Rating cannot exceed 5 stars.',
            'feature_used.required' => 'Please select what best describes your usage.',
            'feature_used.in' => 'Please select a valid feature.',
            'other_user_type.required_if' => 'Please specify your user type when selecting "Other".',
            'other_feature.required_if' => 'Please specify the feature when selecting "Other".',
        ];
    }
}
