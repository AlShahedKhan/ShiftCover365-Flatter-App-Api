<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shift;
use App\Models\ShiftMiddayLog;
use App\Helpers\AuthHelper;
use App\Helpers\ResponseHelper;

class ShiftMiddayLogController extends Controller
{
    // POST /api/shifts/{shift}/midday-log
    public function store(Request $request, $shiftId)
    {
        AuthHelper::checkUser();
        $userId = Auth::id();
        $shift = Shift::findOrFail($shiftId);

        $validated = $request->validate([
            'log_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        // Create the midday log synchronously to return the object in the response
        $middayLog = \App\Models\ShiftMiddayLog::create([
            'shift_id' => $shift->id,
            'user_id' => $userId,
            'log_time' => $validated['log_time'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Dispatch job to send the midday log data via email
        $job = new \App\Jobs\CreateShiftMiddayLog($middayLog->shift_id, $middayLog->user_id, $middayLog->log_time, $middayLog->notes);
        dispatch($job);

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Midday log created successfully',
            'midday_log' => $middayLog
        ], 200);
    }

    // GET /api/shifts/{shift}/midday-logs
    public function index($shiftId)
    {
        AuthHelper::checkUser();
        $shift = Shift::findOrFail($shiftId);
        $logs = ShiftMiddayLog::where('shift_id', $shift->id)->orderBy('log_time')->get();
        return ResponseHelper::success([
            'midday_logs' => $logs
        ], 'Midday logs retrieved successfully', 200);
    }
}
