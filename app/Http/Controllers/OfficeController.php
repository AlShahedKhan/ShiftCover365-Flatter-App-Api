<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Resources\OfficeResource;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::latest()->paginate(10);
        return OfficeResource::collection($offices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'    => 'required|string|max:255',
            'branch_name'     => 'nullable|string|max:255',
            'experience'      => 'nullable|string|max:255',
            'employee_id'     => 'nullable|string|max:50',
            'smart_id_image'  => 'nullable|string|max:255', // Optional: adjust if file upload
        ]);

        $office = Office::create($validated);
        return new OfficeResource($office);
    }

    public function show(Office $office)
    {
        return new OfficeResource($office);
    }

    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
            'company_name'    => 'required|string|max:255',
            'branch_name'     => 'nullable|string|max:255',
            'experience'      => 'nullable|string|max:255',
            'employee_id'     => 'nullable|string|max:50',
            'smart_id_image'  => 'nullable|string|max:255',
        ]);

        $office->update($validated);
        return new OfficeResource($office);
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return response()->json(['message' => 'Office deleted successfully']);
    }
}
