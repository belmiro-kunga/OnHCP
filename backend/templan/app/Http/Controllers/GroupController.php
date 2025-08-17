<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Group::withCount('users')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:190','unique:groups,name'],
            'description' => ['nullable','string','max:255']
        ]);
        $group = Group::create($validated);
        return response()->json(['data' => $group], 201);
    }

    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name' => ['sometimes','string','max:190', Rule::unique('groups','name')->ignore($group->id)],
            'description' => ['nullable','string','max:255']
        ]);
        $group->update($validated);
        return response()->json(['data' => $group]);
    }

    public function destroy(Group $group)
    {
        $group->delete();
        return response()->json(['message' => 'deleted']);
    }

    public function members(Group $group)
    {
        return response()->json(['data' => $group->users()->select('users.id','users.name','users.email','group_user.role')->get()]);
    }

    public function addMember(Request $request, Group $group)
    {
        $validated = $request->validate([
            'user_id' => ['required','exists:users,id'],
            'role' => ['nullable','string','max:190']
        ]);
        if (!$group->users()->where('users.id',$validated['user_id'])->exists()) {
            $group->users()->attach($validated['user_id'], ['role' => $validated['role'] ?? null]);
        }
        return response()->json(['message' => 'added']);
    }

    public function removeMember(Group $group, User $user)
    {
        $group->users()->detach($user->id);
        return response()->json(['message' => 'removed']);
    }
}
