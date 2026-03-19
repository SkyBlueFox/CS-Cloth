<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $this->extractToken($request);

        if (!$plainToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = ApiToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        if (!$token || !$token->user || ($token->expires_at && $token->expires_at->isPast())) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('apiToken', $token);
        $request->setUserResolver(fn () => $token->user);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            return $bearerToken;
        }

        $header = $request->header('Authorization');
        if (!$header) {
            return null;
        }

        return Str::startsWith($header, 'Bearer ') ? Str::after($header, 'Bearer ') : null;
    }
}
