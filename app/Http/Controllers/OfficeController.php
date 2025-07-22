<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Resources\OfficeResource;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;

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
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
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
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
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
