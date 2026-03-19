<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Support\ApiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SuperAdminController extends Controller
{
    public function admins()
    {
        $admins = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])
            ->latest()
            ->get();

        return response()->json([
            'data' => $admins->map(fn (User $user) => ApiData::user($user))->values()->all(),
        ]);
    }

    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_ADMIN,
        ]);

        return response()->json(['user' => ApiData::user($user)], 201);
    }

    public function updateAdmin(Request $request, User $user)
    {
        abort_unless(in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['user' => ApiData::user($user)]);
    }

    public function destroyAdmin(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete yourself.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Admin deleted.']);
    }

    public function users()
    {
        $users = User::query()
            ->where('role', User::ROLE_USER)
            ->latest()
            ->get();

        return response()->json([
            'data' => $users->map(fn (User $user) => ApiData::user($user))->values()->all(),
        ]);
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_USER,
        ]);

        return response()->json(['user' => ApiData::user($user)], 201);
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless(in_array($user->role, [User::ROLE_USER, User::ROLE_ADMIN], true), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['user' => ApiData::user($user)]);
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete yourself.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    public function reports()
    {
        $reports = Report::query()->latest()->paginate(20);

        return response()->json(ApiData::pagination($reports, fn (Report $report) => ApiData::report($report)));
    }

    public function resolve(Report $report)
    {
        Report::query()
            ->where('question_id', $report->question_id)
            ->where('status', Report::STATUS_PENDING)
            ->update(['status' => Report::STATUS_RESOLVED]);

        $question = $report->question;

        if ($question) {
            $question->update([
                'answer_text' => null,
                'admin_id' => null,
                'admin_name' => null,
                'score_cached' => 0,
            ]);
        }

        return response()->json(['message' => 'Report resolved, sibling reports closed, and answer removed.']);
    }

    public function dismiss(Report $report)
    {
        $report->update(['status' => Report::STATUS_DISMISSED]);

        return response()->json(['message' => 'Report dismissed.']);
    }
}
