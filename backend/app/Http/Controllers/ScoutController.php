<?php

namespace App\Http\Controllers;

use App\Models\Scout;
use Illuminate\Http\Request;

class ScoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Scout::with('person.family');

        if ($request->has('den')) {
            $query->where('den', $request->input('den'));
        }

        if ($request->has('rank')) {
            $query->where('rank', $request->input('rank'));
        }

        if ($request->has('status')) {
            $status = $request->input('status');
            $now = now();

            switch ($status) {
                case 'active':
                    $query->where('registration_expiration_date', '>', $now)
                        ->where('registration_status', 'active');
                    break;
                case 'expiring_soon':
                    $query->whereBetween('registration_expiration_date', [
                        $now,
                        $now->copy()->addDays(30)
                    ]);
                    break;
                case 'expired':
                    $query->where('registration_expiration_date', '<', $now);
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
            $query->orderBy('den', 'asc')->orderBy('rank', 'asc');
        }

        $per_page = $request->input('per_page', 25);
        $scouts = $query->paginate($per_page);

        return response()->json($scouts);
    }

    public function show(Scout $scout)
    {
        $scout->load('person.family');
        return response()->json($scout);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:persons,id',
            'grade' => 'nullable|string|max:20',
            'rank' => 'nullable|string|max:50',
            'den' => 'nullable|string|max:50',
            'registration_expiration_date' => 'nullable|date',
            'registration_status' => 'nullable|in:active,inactive,suspended',
            'ypt_status' => 'nullable|in:pending,completed,expired',
            'program' => 'nullable|string|max:50',
        ]);

        $scout = Scout::create($validated);
        $scout->load('person.family');

        return response()->json($scout, 201);
    }

    public function update(Request $request, Scout $scout)
    {
        $validated = $request->validate([
            'person_id' => 'sometimes|required|integer|exists:persons,id',
            'grade' => 'nullable|string|max:20',
            'rank' => 'nullable|string|max:50',
            'den' => 'nullable|string|max:50',
            'registration_expiration_date' => 'nullable|date',
            'registration_status' => 'nullable|in:active,inactive,suspended',
            'ypt_status' => 'nullable|in:pending,completed,expired',
            'program' => 'nullable|string|max:50',
        ]);

        $scout->update($validated);
        $scout->load('person.family');

        return response()->json($scout);
    }

    public function destroy(Scout $scout)
    {
        $scout->delete();
        return response()->json(null, 204);
    }

    public function expiringScouts(Request $request)
    {
        $days = $request->input('days', 60);
        $now = now();

        $scouts = Scout::with('person.family')
            ->whereBetween('registration_expiration_date', [
                $now,
                $now->copy()->addDays($days)
            ])
            ->where('registration_status', 'active')
            ->orderBy('registration_expiration_date', 'asc')
            ->paginate($request->input('per_page', 25));

        return response()->json($scouts);
    }

    public function byDen(Request $request)
    {
        $den = $request->input('den');
        $scouts = Scout::with('person.family')
            ->where('den', $den)
            ->where('registration_status', 'active')
            ->orderBy('rank', 'asc')
            ->get();

        return response()->json($scouts);
    }
}
