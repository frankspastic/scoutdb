<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index(Request $request)
    {
        $query = Family::with('persons', 'scouts', 'parents', 'siblings', 'leaders');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        if ($request->has('sort')) {
            $sort = $request->input('sort');
            $direction = $request->input('direction', 'asc');
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $per_page = $request->input('per_page', 25);
        $families = $query->paginate($per_page);

        return response()->json($families);
    }

    public function show(Family $family)
    {
        $family->load('persons', 'scouts', 'parents', 'siblings', 'leaders');
        return response()->json($family);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:10',
            'primary_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $family = Family::create($validated);
        $family->load('persons', 'scouts', 'parents', 'siblings', 'leaders');

        return response()->json($family, 201);
    }

    public function update(Request $request, Family $family)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:10',
            'primary_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $family->update($validated);
        $family->load('persons', 'scouts', 'parents', 'siblings', 'leaders');

        return response()->json($family);
    }

    public function destroy(Family $family)
    {
        $family->delete();
        return response()->json(null, 204);
    }

    public function merge(Request $request)
    {
        $validated = $request->validate([
            'primary_id' => 'required|integer|exists:families,id',
            'merge_id' => 'required|integer|exists:families,id',
        ]);

        $primary = Family::findOrFail($validated['primary_id']);
        $merge = Family::findOrFail($validated['merge_id']);

        // Move all persons from merge family to primary family
        $merge->persons()->update(['family_id' => $primary->id]);

        // Delete the merged family
        $merge->delete();

        $primary->load('persons', 'scouts', 'parents', 'siblings', 'leaders');
        return response()->json($primary);
    }
}
