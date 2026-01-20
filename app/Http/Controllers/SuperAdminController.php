<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        return redirect()->route('dashboard')->with('success', 'New Admin created successfully!');
    }
}
