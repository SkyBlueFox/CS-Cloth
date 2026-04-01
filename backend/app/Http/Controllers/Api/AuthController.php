<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\EmailOtpChallenge;
use App\Models\User;
use App\Mail\OtpCodeMail;
use App\Support\ApiData;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    private const OTP_TTL_MINUTES = 10;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 5;

    public function registerRequestOtp(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $email = strtolower($validated['email']);

        $this->issueOtp(
            purpose: EmailOtpChallenge::PURPOSE_REGISTER,
            email: $email,
            payload: [
                'name' => $validated['name'],
                'password_hash' => Hash::make($validated['password']),
            ]
        );

        return response()->json([
            'message' => 'OTP sent to email.',
        ]);
    }

    public function registerVerifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = strtolower($validated['email']);
        $challenge = $this->resolveOtpChallenge(
            purpose: EmailOtpChallenge::PURPOSE_REGISTER,
            email: $email,
            otp: $validated['otp']
        );

        if (User::query()->where('email', $email)->exists()) {
            return response()->json([
                'message' => 'This email has already been registered.',
            ], 422);
        }

        $payload = $challenge->payload ?? [];

        $user = User::create([
            'name' => $payload['name'] ?? 'User',
            'email' => $email,
            'password' => $payload['password_hash'] ?? Hash::make(Str::random(24)),
            'role' => User::ROLE_USER,
        ]);

        $challenge->forceFill(['used_at' => now()])->save();

        [$plainTextToken, $token] = $this->createToken($user, 'register');

        return response()->json([
            'data' => [
                'user' => ApiData::user($user),
                'token' => $plainTextToken,
                'token_id' => $token->id,
                'role' => $user->role,
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
                    [
                        'wallet_balance' => $user->balance,
                        'pending_email_change' => $this->pendingEmailChangeForUser($user),
                    ]
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

        $normalizedEmail = strtolower($validated['email']);
        $emailChanged = $normalizedEmail !== strtolower((string) $user->email);

        $user->fill([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if (!$emailChanged || $user->role === User::ROLE_SUPERADMIN) {
            $user->email = $normalizedEmail;
        }

        $user->save();
        $user->load('addresses');

        $message = 'Profile updated.';

        if ($emailChanged && $user->role === User::ROLE_USER) {
            $this->issueOtp(
                purpose: EmailOtpChallenge::PURPOSE_EMAIL_CHANGE,
                email: $normalizedEmail,
                user: $user,
                payload: ['new_email' => $normalizedEmail]
            );

            $message = 'Profile updated. Enter the OTP sent to your new email to finish changing it.';
        }

        return response()->json([
            'message' => $message,
            'data' => [
                'user' => array_merge(
                    ApiData::user($user->fresh()),
                    ['pending_email_change' => $this->pendingEmailChangeForUser($user)]
                ),
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
            'email' => ['required', 'string', 'email', 'exists:users,email'],
        ]);

        $email = strtolower($validated['email']);

        $this->issueOtp(
            purpose: EmailOtpChallenge::PURPOSE_PASSWORD_RESET,
            email: $email
        );

        return response()->json([
            'message' => 'OTP sent to email.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $email = strtolower($validated['email']);
        $challenge = $this->resolveOtpChallenge(
            purpose: EmailOtpChallenge::PURPOSE_PASSWORD_RESET,
            email: $email,
            otp: $validated['otp']
        );

        $user = User::query()->where('email', $email)->firstOrFail();
        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));
        $challenge->forceFill(['used_at' => now()])->save();

        return response()->json([
            'message' => 'Password has been reset.',
        ]);
    }

    public function confirmEmailChange(Request $request)
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_USER) {
            return response()->json([
                'message' => 'Only normal users need OTP confirmation for email changes.',
            ], 403);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $pendingEmail = $this->pendingEmailChangeForUser($user);
        if (!$pendingEmail) {
            return response()->json([
                'message' => 'No pending email change was found.',
            ], 422);
        }

        $challenge = $this->resolveOtpChallenge(
            purpose: EmailOtpChallenge::PURPOSE_EMAIL_CHANGE,
            email: $pendingEmail,
            otp: $validated['otp'],
            user: $user
        );

        if (User::query()->where('email', $pendingEmail)->whereKeyNot($user->id)->exists()) {
            return response()->json([
                'message' => 'That email address is no longer available.',
            ], 422);
        }

        $user->email = $pendingEmail;
        $user->save();

        $challenge->forceFill(['used_at' => now()])->save();

        return response()->json([
            'message' => 'Email updated.',
            'data' => [
                'user' => array_merge(
                    ApiData::user($user->fresh()),
                    ['pending_email_change' => null]
                ),
            ],
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

    private function issueOtp(string $purpose, string $email, ?User $user = null, array $payload = []): void
    {
        $existing = EmailOtpChallenge::query()
            ->where('purpose', $purpose)
            ->where('email', $email)
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if ($existing && $existing->last_sent_at && $existing->last_sent_at->gt(now()->subSeconds(self::OTP_RESEND_COOLDOWN_SECONDS))) {
            throw new HttpResponseException(response()->json([
                'message' => 'Please wait a moment before requesting another OTP.',
            ], 429));
        }

        EmailOtpChallenge::query()
            ->where('purpose', $purpose)
            ->where('email', $email)
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtpChallenge::create([
            'user_id' => $user?->id,
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($otpCode),
            'payload' => $payload,
            'attempts' => 0,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        [$headline, $instruction] = match ($purpose) {
            EmailOtpChallenge::PURPOSE_REGISTER => ['Verify your CS Cloth registration', 'Use this OTP to finish creating your account.'],
            EmailOtpChallenge::PURPOSE_PASSWORD_RESET => ['Reset your CS Cloth password', 'Use this OTP to reset your password.'],
            EmailOtpChallenge::PURPOSE_EMAIL_CHANGE => ['Confirm your new CS Cloth email', 'Use this OTP to confirm your new email address.'],
            default => ['Your CS Cloth OTP', 'Use this OTP to continue.'],
        };

        Mail::to($email)->send(new OtpCodeMail(
            otpCode: $otpCode,
            headline: $headline,
            instruction: $instruction,
            expiresInMinutes: self::OTP_TTL_MINUTES
        ));
    }

    private function resolveOtpChallenge(string $purpose, string $email, string $otp, ?User $user = null): EmailOtpChallenge
    {
        $challenge = EmailOtpChallenge::query()
            ->where('purpose', $purpose)
            ->where('email', $email)
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if (!$challenge || $challenge->expires_at->isPast()) {
            throw new HttpResponseException(response()->json([
                'message' => 'OTP expired or not found. Please request a new one.',
            ], 422));
        }

        if ($challenge->attempts >= self::OTP_MAX_ATTEMPTS) {
            $challenge->forceFill(['used_at' => now()])->save();

            throw new HttpResponseException(response()->json([
                'message' => 'Too many incorrect OTP attempts. Please request a new code.',
            ], 422));
        }

        if (!Hash::check($otp, $challenge->code_hash)) {
            $challenge->increment('attempts');

            throw new HttpResponseException(response()->json([
                'message' => 'Incorrect OTP code.',
            ], 422));
        }

        return $challenge;
    }

    private function pendingEmailChangeForUser(User $user): ?string
    {
        return EmailOtpChallenge::query()
            ->where('purpose', EmailOtpChallenge::PURPOSE_EMAIL_CHANGE)
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest('id')
            ->value('email');
    }
}
