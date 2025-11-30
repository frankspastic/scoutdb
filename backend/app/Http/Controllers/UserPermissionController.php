<?php

namespace App\Http\Controllers;

use App\Models\UserPermission;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = UserPermission::with('person.family', 'grantedBy.person');

        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
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
            $query->orderBy('created_at', 'desc');
        }

        $per_page = $request->input('per_page', 25);
        $permissions = $query->paginate($per_page);

        return response()->json($permissions);
    }

    public function show(UserPermission $permission)
    {
        $permission->load('person.family', 'grantedBy.person');
        return response()->json($permission);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wordpress_user_id' => 'required|integer|unique:user_permissions',
            'person_id' => 'required|integer|exists:persons,id',
            'role' => 'required|in:admin,editor,viewer',
            'granted_by' => 'nullable|integer|exists:user_permissions,id',
            'granted_at' => 'nullable|date',
        ]);

        $validated['granted_at'] = $validated['granted_at'] ?? now();

        $permission = UserPermission::create($validated);
        $permission->load('person.family', 'grantedBy.person');

        return response()->json($permission, 201);
    }

    public function update(Request $request, UserPermission $permission)
    {
        $validated = $request->validate([
            'role' => 'sometimes|required|in:admin,editor,viewer',
            'granted_by' => 'nullable|integer|exists:user_permissions,id',
            'granted_at' => 'nullable|date',
        ]);

        $permission->update($validated);
        $permission->load('person.family', 'grantedBy.person');

        return response()->json($permission);
    }

    public function destroy(UserPermission $permission)
    {
        $permission->delete();
        return response()->json(null, 204);
    }

    public function byRole(Request $request)
    {
        $role = $request->input('role');
        $permissions = UserPermission::with('person.family')
            ->where('role', $role)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($permissions);
    }

    public function byWordPressUser(Request $request)
    {
        $wordpress_user_id = $request->input('wordpress_user_id');
        $permission = UserPermission::with('person.family', 'grantedBy.person')
            ->where('wordpress_user_id', $wordpress_user_id)
            ->first();

        if (!$permission) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        return response()->json($permission);
    }

    public function admins()
    {
        $admins = UserPermission::with('person.family')
            ->where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($admins);
    }
}
