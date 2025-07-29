<?php

namespace App\Http\Controllers;

use App\Models\DiscrepancyLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\ResponseHelper;

class DiscrepancyController extends Controller
{
    // Every 2 hours alert
    public function alert(Request $request)
    {
        try {
            $request->validate([
                'shift_id' => 'required|integer',
                'status' => 'required|in:discrepancy,no_discrepancy',
                'note' => 'nullable|string',
            ]);

            $user = Auth::user();

            $log = DiscrepancyLog::create([
                'user_id' => $user->id,
                'shift_id' => $request->shift_id,
                'status' => $request->status,
                'note' => $request->note,
                'type' => 'auto',
            ]);

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Discrepancy Alert',
                'body' => $request->status === 'discrepancy' ? 'A discrepancy was reported.' : 'No discrepancy reported.',
                'is_read' => false,
                'type' => 'discrepancy',
                'data' => null,
            ]);

            return ResponseHelper::success([
                'log' => $log
            ], 'Discrepancy status updated.', 201);

        } catch (\Exception $e) {
            Log::error('Discrepancy alert error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseHelper::error('Failed to log discrepancy alert. Please try again.', 500);
        }
    }

}
