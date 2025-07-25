<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuthHelper;
use App\Helpers\ResponseHelper;

class ShiftController extends Controller
{
    // GET /api/shifts
    public function index()
    {
        AuthHelper::checkUser();

        $userId = Auth::id();

        \Log::info('Authenticated user ID: ' . $userId);
        \Log::info('All shifts: ', Shift::select('id', 'user_id')->get()->toArray());

        $shifts = Shift::with(['office', 'shiftType'])->get();


        return ResponseHelper::success([
            'shifts' => $shifts
        ], 'Shifts retrieved successfully', 200);
    }


    // POST /api/shifts
    public function store(Request $request)
    {
        AuthHelper::checkUser();

        $validated = $request->validate([
            'office_id' => 'required|exists:offices,id',
            'shift_type_id' => 'required|exists:shift_types,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string',
            'department' => 'required|string',
            'budget' => 'nullable|numeric',
        ]);

        $validated['user_id'] = Auth::id();

        $shift = Shift::create($validated);

        return ResponseHelper::success([
            'shift' => $shift
        ], 'Shift created successfully', 201);
    }

    // GET /api/shifts/{id}
    public function show(Shift $shift)
    {
        AuthHelper::checkUser();
        $this->authorizeOwner($shift);

        return ResponseHelper::success([
            'shift' => $shift->load(['office', 'shiftType'])
        ], 'Shift retrieved successfully', 200);
    }

    // PUT/PATCH /api/shifts/{id}
    public function update(Request $request, Shift $shift)
    {
        AuthHelper::checkUser();
        $this->authorizeOwner($shift);

        $validated = $request->validate([
            'office_id' => 'sometimes|exists:offices,id',
            'shift_type_id' => 'sometimes|exists:shift_types,id',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'location' => 'sometimes|string',
            'department' => 'sometimes|string',
            'budget' => 'nullable|numeric',
        ]);

        $shift->update($validated);

        return ResponseHelper::success([
            'shift' => $shift
        ], 'Shift updated successfully', 200);
    }

    // DELETE /api/shifts/{id}
    public function destroy(Shift $shift)
    {
        AuthHelper::checkUser();
        $this->authorizeOwner($shift);

        $shift->delete();

        return ResponseHelper::success([
            'shift' => $shift
        ], 'Shift deleted successfully', 200);
    }

    // Authorization helper
    protected function authorizeOwner(Shift $shift)
    {
        if ($shift->user_id !== Auth::id()) {
            abort(403, 'Forbidden: You do not own this shift.');
        }
    }
}
