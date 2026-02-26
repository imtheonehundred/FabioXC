<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResellerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        $groupName = $user->group?->group_name ?? '';
        if (!in_array($groupName, ['Resellers', 'Administrators'])) {
            abort(403, 'Access denied. Reseller privileges required.');
        }

        return $next($request);
    }
}
