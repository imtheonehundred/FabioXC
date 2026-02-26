<?php

namespace App\Streaming\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Parses authentication tokens from streaming requests.
 * Supports: username/password in URL, HMAC tokens, bearer tokens.
 */
class TokenAuth
{
    public function parse(Request $request): ?array
    {
        // Method 1: username/password in query string (standard XC format)
        // e.g. /live/username/password/stream_id.ts
        $username = $request->input('username') ?? $request->route('username');
        $password = $request->input('password') ?? $request->route('password');

        if ($username && $password) {
            return $this->resolveByCredentials($username, $password);
        }

        // Method 2: Bearer token
        $bearer = $request->bearerToken();
        if ($bearer) {
            return $this->resolveByToken($bearer);
        }

        // Method 3: Token in query string
        $token = $request->input('token');
        if ($token) {
            return $this->resolveByToken($token);
        }

        // Method 4: Parse from URL path segments (/live/{username}/{password}/{stream}.ts)
        $path = $request->path();
        $segments = explode('/', trim($path, '/'));
        if (count($segments) >= 3) {
            $pathUser = $segments[count($segments) - 3] ?? null;
            $pathPass = $segments[count($segments) - 2] ?? null;
            if ($pathUser && $pathPass) {
                $result = $this->resolveByCredentials($pathUser, $pathPass);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    public function parseStreamId(Request $request): ?int
    {
        $streamId = $request->input('stream_id') ?? $request->route('stream_id');
        if ($streamId) {
            return (int) $streamId;
        }

        $path = $request->path();
        if (preg_match('/(\d+)\.\w+$/', $path, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function resolveByCredentials(string $username, string $password): ?array
    {
        $line = DB::table('lines')
            ->where('username', $username)
            ->where('password', $password)
            ->first();

        if (!$line) {
            return null;
        }

        return [
            'line_id' => $line->id,
            'username' => $line->username,
            'password' => $line->password,
            'exp_date' => $line->exp_date,
            'max_connections' => $line->max_connections,
            'active_connections' => $line->active_connections,
            'admin_enabled' => (bool) $line->admin_enabled,
            'is_trial' => (bool) $line->is_trial,
            'is_restreamer' => (bool) $line->is_restreamer,
            'bouquet' => json_decode($line->bouquet, true) ?? [],
            'allowed_ips' => $line->allowed_ips,
            'allowed_ua' => $line->allowed_ua,
            'auth_method' => 'credentials',
        ];
    }

    private function resolveByToken(string $token): ?array
    {
        // HMAC token format: base64(json({username, exp, sig}))
        $decoded = base64_decode($token, true);
        if (!$decoded) {
            return null;
        }

        $payload = json_decode($decoded, true);
        if (!$payload || !isset($payload['username'])) {
            return null;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        $line = DB::table('lines')
            ->where('username', $payload['username'])
            ->first();

        if (!$line) {
            return null;
        }

        return [
            'line_id' => $line->id,
            'username' => $line->username,
            'password' => $line->password,
            'exp_date' => $line->exp_date,
            'max_connections' => $line->max_connections,
            'active_connections' => $line->active_connections,
            'admin_enabled' => (bool) $line->admin_enabled,
            'is_trial' => (bool) $line->is_trial,
            'is_restreamer' => (bool) $line->is_restreamer,
            'bouquet' => json_decode($line->bouquet, true) ?? [],
            'allowed_ips' => $line->allowed_ips,
            'allowed_ua' => $line->allowed_ua,
            'auth_method' => 'token',
        ];
    }
}
