<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use App\Support\ApiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_USER,
        ]);

        [$plainTextToken, $token] = $this->createToken($user, 'register');

        return response()->json([
            'data' => [
                'user' => ApiData::user($user),
                'token' => $plainTextToken,
                'token_id' => $token->id,
                'role' => $user->role
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', strtolower($validated['email']))
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 422);
        }

        [$plainTextToken, $token] = $this->createToken($user, 'login');

        return response()->json([
            'data' => [
                'user' => ApiData::user($user),
                'token' => $plainTextToken,
                'token_id' => $token->id,
                'role' => $user->role
            ]
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('addresses');

        return response()->json([
            'data' => [
                'user' => array_merge(
                    ApiData::user($user),
                    ['wallet_balance' => $user->balance]
                ),
                'addresses' => $user->addresses
                    ->map(fn ($address) => ApiData::address($address))
                    ->values()
                    ->all()
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->attributes->get('apiToken')?->delete();

        return response()->json([
            'message' => 'Logged out.'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if ($user->role === User::ROLE_ADMIN) {
            return response()->json([
                'message' => 'Admins cannot update their profile.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', PasswordRule::defaults()],
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
            'data' => [
                'user' => ApiData::user($user->fresh()),
                'addresses' => $user->addresses
                    ->map(fn ($address) => ApiData::address($address))
                    ->values()
                    ->all()
            ]
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = PasswordBroker::sendResetLink([
            'email' => strtolower($validated['email']),
        ]);

        if ($status !== PasswordBroker::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => 'Password reset link sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = PasswordBroker::reset(
            [
                'email' => strtolower($validated['email']),
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
                'token' => $validated['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => 'Password has been reset.',
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
