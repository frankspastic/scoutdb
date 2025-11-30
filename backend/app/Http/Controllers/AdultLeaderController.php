<?php

namespace App\Http\Controllers;

use App\Models\AdultLeader;
use Illuminate\Http\Request;

class AdultLeaderController extends Controller
{
    public function index(Request $request)
    {
        $query = AdultLeader::with('person.family');

        if ($request->has('ypt_status')) {
            $ypt_status = $request->input('ypt_status');
            switch ($ypt_status) {
                case 'expired':
                    $query->where('ypt_expiration_date', '<', now());
                    break;
                case 'expiring_soon':
                    $query->whereBetween('ypt_expiration_date', [
                        now(),
                        now()->addDays(30)
                    ]);
                    break;
                case 'current':
                    $query->where('ypt_expiration_date', '>', now()->addDays(30));
                    break;
                case 'unknown':
                    $query->whereNull('ypt_expiration_date');
                    break;
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('person', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort')) {
            $sort = $request->input('sort');
            $direction = $request->input('direction', 'asc');
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('ypt_expiration_date', 'asc');
        }

        $per_page = $request->input('per_page', 25);
        $leaders = $query->paginate($per_page);

        return response()->json($leaders);
    }

    public function show(AdultLeader $leader)
    {
        $leader->load('person.family');
        return response()->json($leader);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:persons,id',
            'positions' => 'nullable|array',
            'positions.*' => 'string|max:100',
            'ypt_status' => 'nullable|in:pending,completed,expired',
            'ypt_completion_date' => 'nullable|date',
            'ypt_expiration_date' => 'nullable|date',
            'registration_expiration_date' => 'nullable|date',
        ]);

        $leader = AdultLeader::create($validated);
        $leader->load('person.family');

        return response()->json($leader, 201);
    }

    public function update(Request $request, AdultLeader $leader)
    {
        $validated = $request->validate([
            'person_id' => 'sometimes|required|integer|exists:persons,id',
            'positions' => 'nullable|array',
            'positions.*' => 'string|max:100',
            'ypt_status' => 'nullable|in:pending,completed,expired',
            'ypt_completion_date' => 'nullable|date',
            'ypt_expiration_date' => 'nullable|date',
            'registration_expiration_date' => 'nullable|date',
        ]);

        $leader->update($validated);
        $leader->load('person.family');

        return response()->json($leader);
    }

    public function destroy(AdultLeader $leader)
    {
        $leader->delete();
        return response()->json(null, 204);
    }

    public function expiringSoon(Request $request)
    {
        $days = $request->input('days', 30);
        $now = now();

        $leaders = AdultLeader::with('person.family')
            ->whereBetween('ypt_expiration_date', [
                $now,
                $now->copy()->addDays($days)
            ])
            ->orderBy('ypt_expiration_date', 'asc')
            ->paginate($request->input('per_page', 25));

        return response()->json($leaders);
    }

    public function addPosition(Request $request, AdultLeader $leader)
    {
        $validated = $request->validate([
            'position' => 'required|string|max:100',
        ]);

        $positions = $leader->positions ?? [];
        if (!in_array($validated['position'], $positions)) {
            $positions[] = $validated['position'];
            $leader->update(['positions' => $positions]);
        }

        $leader->load('person.family');
        return response()->json($leader);
    }

    public function removePosition(Request $request, AdultLeader $leader)
    {
        $validated = $request->validate([
            'position' => 'required|string|max:100',
        ]);

        $positions = $leader->positions ?? [];
        $positions = array_filter($positions, fn($p) => $p !== $validated['position']);
        $leader->update(['positions' => array_values($positions)]);

        $leader->load('person.family');
        return response()->json($leader);
    }
}
