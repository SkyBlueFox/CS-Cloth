<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use App\Support\ApiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
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

        [$plainTextToken, $token] = $this->createToken($user, 'register');

        return response()->json([
            'user' => ApiData::user($user),
            'token' => $plainTextToken,
            'token_id' => $token->id,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', strtolower($validated['email']))->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        [$plainTextToken, $token] = $this->createToken($user, 'login');

        return response()->json([
            'user' => ApiData::user($user),
            'token' => $plainTextToken,
            'token_id' => $token->id,
        ]);
    }

    public function me(Request $request)
    {
        $request->user()->load('addresses');

        return response()->json([
            'user' => ApiData::user($request->user()),
            'addresses' => $request->user()->addresses->map(fn ($address) => ApiData::address($address))->values()->all(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->attributes->get('apiToken')?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->load('addresses');

        return response()->json([
            'user' => ApiData::user($user->fresh()),
            'addresses' => $user->addresses->map(fn ($address) => ApiData::address($address))->values()->all(),
        ]);
    }

    private function createToken(User $user, string $name): array
    {
        $plainTextToken = Str::random(80);

        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => $name,
            'token_hash' => hash('sha256', $plainTextToken),
            'last_used_at' => now(),
        ]);

        return [$plainTextToken, $token];
    }
}
