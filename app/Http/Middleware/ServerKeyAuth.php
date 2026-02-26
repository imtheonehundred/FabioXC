<?php

namespace App\Http\Middleware;

use App\Domain\Server\Models\Server;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->bearerToken() ?? $request->input('server_key');

        if (!$key) {
            return response()->json(['error' => 'Server key required'], 401);
        }

        $server = Server::where('server_key', $key)->first();

        if (!$server) {
            return response()->json(['error' => 'Invalid server key'], 401);
        }

        $request->merge(['api_server' => $server]);

        return $next($request);
    }
}
