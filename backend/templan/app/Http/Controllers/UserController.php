<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    // GET /api/users
    public function index(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $query = User::with(['role', 'department']);

        if ($q !== '') {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // Optional status filter if column exists
        if (!is_null($status) && Schema::hasColumn('users', 'status')) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    // GET /api/users/{user}
    public function show(User $user)
    {
        $user->load(['role', 'department']);
        return response()->json(['data' => $user]);
    }

    // POST /api/users
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'nullable|string',
        ]);
        $validator->validate();

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        if (!empty($data['password'])) {
            $payload['password'] = $data['password'];
        } else {
            $payload['password'] = Str::random(10);
        }
        
        // Add optional fields
        if (isset($data['role_id'])) $payload['role_id'] = $data['role_id'];
        if (isset($data['department_id'])) $payload['department_id'] = $data['department_id'];
        if (isset($data['status'])) $payload['status'] = $data['status'];

        $user = User::create($payload);
        $user->load(['role', 'department']);
        return response()->json(['data' => $user], 201);
    }

    // PUT /api/users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'nullable|string',
        ]);
        $validator->validate();

        if (array_key_exists('name', $data)) $user->name = $data['name'];
        if (array_key_exists('email', $data)) $user->email = $data['email'];
        if (!empty($data['password'])) $user->password = $data['password'];
        if (array_key_exists('role_id', $data)) $user->role_id = $data['role_id'];
        if (array_key_exists('department_id', $data)) $user->department_id = $data['department_id'];
        if (array_key_exists('status', $data)) $user->status = $data['status'];
        $user->save();

        $user->load(['role', 'department']);
        return response()->json(['data' => $user]);
    }

    // DELETE /api/users/{user}
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['data' => true]);
    }

    // POST /api/users/{user}/reset-password
    public function resetPassword(User $user)
    {
        $new = Str::random(12);
        $user->password = $new;
        $user->save();
        // In a real app, email the user the reset link or token.
        return response()->json(['data' => ['reset' => true, 'temporaryPassword' => $new]]);
    }

    // POST /api/users/{user}/enable-mfa
    public function enableMfa(User $user)
    {
        if (Schema::hasColumn('users', 'mfa_enabled')) {
            $user->mfa_enabled = true;
            $user->save();
            return response()->json(['data' => ['mfa_enabled' => true]]);
        }
        return response()->json(['message' => 'MFA column not available'], 400);
    }

    // POST /api/users/{user}/disable-mfa
    public function disableMfa(User $user)
    {
        if (Schema::hasColumn('users', 'mfa_enabled')) {
            $user->mfa_enabled = false;
            $user->save();
            return response()->json(['data' => ['mfa_enabled' => false]]);
        }
        return response()->json(['message' => 'MFA column not available'], 400);
    }

    // POST /api/users/bulk
    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids = (array) $request->input('ids', []);

        if ($action === 'delete') {
            User::whereIn('id', $ids)->delete();
            return response()->json(['data' => ['deleted' => count($ids)]]);
        }

        if ($action === 'status' && Schema::hasColumn('users', 'status')) {
            $status = $request->input('status');
            User::whereIn('id', $ids)->update(['status' => $status]);
            return response()->json(['data' => ['updated' => count($ids), 'status' => $status]]);
        }

        return response()->json(['message' => 'Unsupported bulk action'], 400);
    }

    // POST /api/users/import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);
        $file = $request->file('file');
        $created = 0;

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // skip header if present
            $first = true;
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                if ($first && isset($row[0]) && stripos($row[0], 'name') !== false) { $first = false; continue; }
                $first = false;
                $name = $row[0] ?? null;
                $email = $row[1] ?? null;
                if ($name && $email) {
                    User::firstOrCreate(['email' => $email], [
                        'name' => $name,
                        'password' => Str::random(10),
                    ]);
                    $created++;
                }
            }
            fclose($handle);
        }

        return response()->json(['data' => ['created' => $created]]);
    }

    // GET /api/users/export
    public function export(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'email', 'created_at']);
            User::orderBy('id')->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $u) {
                    fputcsv($handle, [$u->id, $u->name, $u->email, $u->created_at]);
                }
            });
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="users.csv"');
        return $response;
    }
}
