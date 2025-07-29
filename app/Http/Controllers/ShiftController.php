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
        $shifts = Shift::with(['office', 'shiftType'])->get();


        return ResponseHelper::success([
            'shifts' => $shifts
        ], 'Shifts retrieved successfully', 200);
    }


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

    public function myShiftCount()
    {
        AuthHelper::checkUser();

        $userId = Auth::id();

        $count = Shift::where('user_id', $userId)->count();

        return ResponseHelper::success([
            'shift_count' => $count
        ], 'Total shifts posted by the user', 200);
    }

    /**
     * List all shifts for professional users
     */
    public function allShiftsForProfessionals(Request $request)
    {
        AuthHelper::checkUser();
        $user = Auth::user();
        if ($user->role !== 'professional') {
            return ResponseHelper::error('Only professionals can view all shifts', 403);
        }
        $shifts = Shift::with(['office', 'shiftType'])->get();
        return ResponseHelper::success([
            'shifts' => $shifts
        ], 'All shifts for professionals', 200);
    }

    /**
     * Professional applies for a shift
     */
    public function applyForShift(Request $request, $shiftId)
    {
        AuthHelper::checkUser();
        $user = Auth::user();
        if ($user->role !== 'professional') {
            return ResponseHelper::error('Only professionals can apply for shifts', 403);
        }
        $shift = Shift::findOrFail($shiftId);
        // Prevent duplicate application
        $existing = $shift->applications()->where('user_id', $user->id)->first();
        if ($existing) {
            return ResponseHelper::error('You have already applied for this shift', 409);
        }
        $application = $shift->applications()->create([
            'user_id' => $user->id,
            'status' => 'new',
        ]);
        return ResponseHelper::success([
            'application' => $application
        ], 'Applied for shift successfully', 201);
    }

    /**
     * Manager: Accept or reject a shift application
     */
    public function updateApplicationStatus(Request $request, $applicationId)
    {
        AuthHelper::checkUser();
        $user = Auth::user();
        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can update application status', 403);
        }
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);
        $application = \App\Models\ShiftApplication::findOrFail($applicationId);
        // Ensure the manager owns the shift
        if ($application->shift->user_id !== $user->id) {
            return ResponseHelper::error('You do not have permission to update this application', 403);
        }
        $application->status = $request->status;
        $application->save();
        return ResponseHelper::success([
            'application' => $application
        ], 'Application status updated', 200);
    }

    /**
     * Search for shifts (location, date, department)
     */
    public function search(Request $request)
    {
        AuthHelper::checkUser();
        $query = Shift::query();
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        if ($request->filled('department')) {
            $query->where('department', 'like', '%' . $request->department . '%');
        }
        if ($request->filled('date')) {
            // Assuming you have a date field or can filter by start_time
            $query->whereDate('created_at', $request->date);
        }
        $shifts = $query->with(['office', 'shiftType'])->get();
        return ResponseHelper::success([
            'shifts' => $shifts
        ], 'Shifts search results', 200);
    }

    /**
     * Show a single shift for professionals
     */
    public function showForProfessional($shiftId)
    {
        AuthHelper::checkUser();
        $user = Auth::user();
        if ($user->role !== 'professional') {
            return ResponseHelper::error('Only professionals can view this shift', 403);
        }
        $shift = Shift::with(['office', 'shiftType'])->findOrFail($shiftId);
        return ResponseHelper::success([
            'shift' => $shift
        ], 'Shift retrieved successfully', 200);
    }

    /**
     * Manager: View all applications for shifts they created
     */
    public function applicationsForMyShifts()
    {
        AuthHelper::checkUser();
        $user = Auth::user();
        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can view applications for their shifts', 403);
        }
        // Get all shifts created by this manager
        $shiftIds = \App\Models\Shift::where('user_id', $user->id)->pluck('id');
        // Get all applications for these shifts, with professional user info and shift info
        $applications = \App\Models\ShiftApplication::with(['user', 'shift.office', 'shift.shiftType'])
            ->whereIn('shift_id', $shiftIds)
            ->get();
        return ResponseHelper::success([
            'applications' => $applications
        ], 'Applications for your shifts', 200);
    }

        /**
     * Manager: Get shifts by specific date
     */
    public function getShiftsByDateForManagers($date)
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can view shifts by date', 403);
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return ResponseHelper::error('Invalid date format. Use YYYY-MM-DD format', 400);
        }

        // Get shifts for the specific date (only shifts created by this manager)
        $shifts = Shift::with(['office', 'shiftType'])
            ->where('user_id', $user->id)
            ->whereDate('created_at', $date)
            ->get();

        return ResponseHelper::success([
            'shifts' => $shifts,
            'date' => $date
        ], 'Shifts for the specified date retrieved successfully', 200);
    }

    // Authorization helper
    protected function authorizeOwner(Shift $shift)
    {
        if ($shift->user_id !== Auth::id()) {
            abort(403, 'Forbidden: You do not own this shift.');
        }
    }

    /**
     * Start a shift (set actual start time)
     */
    public function startShift(Request $request, $shiftId)
    {
        AuthHelper::checkUser();
        $user = Auth::user();
        $shift = Shift::findOrFail($shiftId);
        // Only assigned professional or owner can start
        if ($shift->user_id !== $user->id) {
            return ResponseHelper::error('You do not have permission to start this shift', 403);
        }
        $request->validate([
            'start_time' => 'required|date_format:H:i',
        ]);
        $shift->start_time = $request->start_time;
        $shift->save();
        return ResponseHelper::success([
            'shift' => $shift
        ], 'Shift start time updated', 200);
    }

    /**
     * End a shift (set actual end time)
     */
    public function endShift(Request $request, $shiftId)
    {
        AuthHelper::checkUser();
        $user = Auth::user();
        $shift = Shift::findOrFail($shiftId);
        // Only assigned professional or owner can end
        if ($shift->user_id !== $user->id) {
            return ResponseHelper::error('You do not have permission to end this shift', 403);
        }
        $request->validate([
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);
        $shift->end_time = $request->end_time;
        $shift->save();
        return ResponseHelper::success([
            'shift' => $shift
        ], 'Shift end time updated', 200);
    }
}
