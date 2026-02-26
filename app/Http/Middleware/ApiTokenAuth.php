<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        $token = $request->bearerToken() ?? $request->input('api_key');

        if (!$token) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $user = User::where('api_key', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        if ($role === 'reseller' && !in_array($user->group?->group_name, ['Resellers', 'Administrators'])) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $request->merge(['api_user' => $user]);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
