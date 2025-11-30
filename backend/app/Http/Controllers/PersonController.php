<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $query = Person::with('family', 'scout', 'leader');

        if ($request->has('family_id')) {
            $query->where('family_id', $request->input('family_id'));
        }

        if ($request->has('person_type')) {
            $query->where('person_type', $request->input('person_type'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort')) {
            $sort = $request->input('sort');
            $direction = $request->input('direction', 'asc');
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('last_name', 'asc')->orderBy('first_name', 'asc');
        }

        $per_page = $request->input('per_page', 25);
        $persons = $query->paginate($per_page);

        return response()->json($persons);
    }

    public function show(Person $person)
    {
        $person->load('family', 'scout', 'leader');
        return response()->json($person);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_id' => 'nullable|integer|exists:families,id',
            'person_type' => 'required|in:scout,parent,sibling,adult_leader',
            'bsa_member_id' => 'nullable|string|max:20|unique:persons',
            'prefix' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'nickname' => 'nullable|string|max:255',
            'gender' => 'nullable|in:M,F,Other',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:150',
            'email' => 'nullable|email|unique:persons',
            'phone' => 'nullable|string|max:20',
        ]);

        $person = Person::create($validated);
        $person->load('family', 'scout', 'leader');

        return response()->json($person, 201);
    }

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'family_id' => 'nullable|integer|exists:families,id',
            'person_type' => 'sometimes|required|in:scout,parent,sibling,adult_leader',
            'bsa_member_id' => 'nullable|string|max:20|unique:persons,bsa_member_id,' . $person->id,
            'prefix' => 'nullable|string|max:10',
            'first_name' => 'sometimes|required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'nickname' => 'nullable|string|max:255',
            'gender' => 'nullable|in:M,F,Other',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:150',
            'email' => 'nullable|email|unique:persons,email,' . $person->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $person->update($validated);
        $person->load('family', 'scout', 'leader');

        return response()->json($person);
    }

    public function destroy(Person $person)
    {
        $person->delete();
        return response()->json(null, 204);
    }

    public function searchOrphaned(Request $request)
    {
        $search = $request->input('search', '');
        $persons = Person::orphaned()
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25);

        return response()->json($persons);
    }

    public function merge(Request $request)
    {
        $validated = $request->validate([
            'primary_id' => 'required|integer|exists:persons,id',
            'merge_id' => 'required|integer|exists:persons,id',
        ]);

        $primary = Person::findOrFail($validated['primary_id']);
        $merge = Person::findOrFail($validated['merge_id']);

        // Update any scout/leader records
        if ($merge->scout) {
            $merge->scout->update(['person_id' => $primary->id]);
        }
        if ($merge->leader) {
            $merge->leader->update(['person_id' => $primary->id]);
        }

        // Delete the merged person
        $merge->delete();

        $primary->load('family', 'scout', 'leader');
        return response()->json($primary);
    }
}
