<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuthHelper;
use App\Helpers\ResponseHelper;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        AuthHelper::checkUser();

        $faqs = Faq::with('user')->get();

        return ResponseHelper::success([
            'faqs' => $faqs
        ], 'FAQs retrieved successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        AuthHelper::checkUser();

        // Only managers and admins can create FAQs
        $user = Auth::user();
        if (!in_array($user->role, ['manager', 'admin'])) {
            return ResponseHelper::error('Only managers and admins can create FAQs', 403);
        }

        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string',
        ]);

        $validated['user_id'] = Auth::id();

        $faq = Faq::create($validated);

        return ResponseHelper::success([
            'faq' => $faq->load('user')
        ], 'FAQ created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq $faq)
    {
        AuthHelper::checkUser();

        return ResponseHelper::success([
            'faq' => $faq->load('user')
        ], 'FAQ retrieved successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        AuthHelper::checkUser();

        // Only managers and admins can update FAQs
        $user = Auth::user();
        if (!in_array($user->role, ['manager', 'admin'])) {
            return ResponseHelper::error('Only managers and admins can update FAQs', 403);
        }

        $validated = $request->validate([
            'question' => 'sometimes|required|string|max:1000',
            'answer' => 'sometimes|required|string',
        ]);

        $faq->update($validated);

        return ResponseHelper::success([
            'faq' => $faq->load('user')
        ], 'FAQ updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {
        AuthHelper::checkUser();

        // Only managers and admins can delete FAQs
        $user = Auth::user();
        if (!in_array($user->role, ['manager', 'admin'])) {
            return ResponseHelper::error('Only managers and admins can delete FAQs', 403);
        }

        $faq->delete();

        return ResponseHelper::success([
            'faq' => $faq
        ], 'FAQ deleted successfully', 200);
    }
}
