<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Http\Requests\ContactFormRequest;
use App\Jobs\Contact\SendContactEmail;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Submit contact form
     */
    public function submit(ContactFormRequest $request)
    {
        try {
            // Create contact record
            $contact = Contact::create($request->validated());

            // Dispatch email job to queue
            SendContactEmail::dispatch($contact);

            return ResponseHelper::success([
                'contact' => $contact
            ], 'Contact form submitted successfully. We will get back to you soon.', 201);

        } catch (\Exception $e) {
            Log::error('Contact form submission error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseHelper::error('Failed to submit contact form. Please try again.', 500);
        }
    }
}
