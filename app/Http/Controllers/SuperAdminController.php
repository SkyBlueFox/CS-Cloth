<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class SuperAdminController extends Controller
{
    public function createAdmin()
    {
        $admins = User::where('role', User::ROLE_ADMIN)
            ->orderBy('created_at', 'desc')
            ->get();

        // Pass the $admins variable to the view
        return view('superadmin.create-admin', compact('admins'));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);


        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_ADMIN,
        ]);

        return back()->with('success', 'New Admin created successfully!');
    }

    public function createUser()
    {
        $users = User::where('role', User::ROLE_USER)
            ->orderBy('created_at', 'desc')
            ->get();

        // Pass the $admins variable to the view
        return view('superadmin.create-user', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);


        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_USER,
        ]);

        return back()->with('success', 'New User created successfully!');
    }

    public function editAdmin(User $user)
    {
        // Ensure the user being edited is actually an admin
        if ($user->role !== User::ROLE_ADMIN && $user->role !== User::ROLE_SUPERADMIN) {
            abort(403, 'This user is not an admin.');
        }

        return view('superadmin.edit-admin', ['admin' => $user]);
    }

    public function updateAdmin(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Unique email, but ignore the current user's email so it doesn't fail if they don't change it
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Only update password if they typed something
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('superadmin.create-admin')->with('success', 'Admin updated successfully.');
    }

    public function destroyAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return back()->with('success', 'Admin deleted successfully.');
    }

    public function editUser(User $user)
    {
        // Ensure the user being edited is actually a user
        if ($user->role !== User::ROLE_ADMIN && $user->role !== User::ROLE_USER) {
            abort(403, 'This user is not an admin.');
        }

        return view('superadmin.edit-user', ['user' => $user]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Unique email, but ignore the current user's email so it doesn't fail if they don't change it
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Only update password if they typed something
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('superadmin.create-user')->with('success', 'User updated successfully.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function reports()
    {
        $reports = Report::latest()->paginate(10);

        return view('superadmin.reports', compact('reports'));
    }

    public function resolve(Report $report)
    {
        $report->update([
            'status' => Report::STATUS_RESOLVED
        ]);

        return back()->with('success', 'Report resolved successfully.');
    }

    public function dismiss(Report $report)
    {
        $report->update([
            'status' => Report::STATUS_DISMISSED
        ]);

        return back()->with('success', 'Report dismissed.');
    }
}
