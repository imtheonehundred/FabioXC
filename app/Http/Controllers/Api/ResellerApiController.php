<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Line;
use App\Domain\Line\Models\Package;
use App\Domain\Server\Models\Server;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResellerApiController extends Controller
{
    private function reseller(Request $request): User
    {
        return $request->user();
    }

    public function getProfile(Request $request): JsonResponse
    {
        $user = $this->reseller($request);
        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'group' => $user->group?->group_name,
            'created_at' => $user->created_at,
        ]);
    }

    // ─── Lines (scoped to created_by) ─────────────────────

    public function listLines(Request $request): JsonResponse
    {
        $query = Line::where('created_by', $this->reseller($request)->id)->with('packages');

        if ($request->filled('search')) {
            $query->where('username', 'like', '%' . $request->input('search') . '%');
        }

        return response()->json(
            $query->orderByDesc('id')->paginate($request->input('per_page', 25))
        );
    }

    public function getLine(Request $request, int $id): JsonResponse
    {
        $line = Line::where('created_by', $this->reseller($request)->id)
            ->with('packages')
            ->findOrFail($id);
        return response()->json($line);
    }

    public function createLine(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|unique:lines,username|max:255',
            'password' => 'required|string|max:255',
            'exp_date' => 'nullable|date',
            'max_connections' => 'required|integer|min:1|max:10',
            'is_trial' => 'boolean',
            'admin_enabled' => 'boolean',
            'bouquet' => 'nullable|array',
            'notes' => 'nullable|string',
            'package_ids' => 'nullable|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        $packageIds = $data['package_ids'] ?? [];
        unset($data['package_ids']);
        $data['added'] = now();
        $data['created_by'] = $this->reseller($request)->id;

        $line = Line::create($data);
        if ($packageIds) $line->packages()->attach($packageIds);

        return response()->json($line->load('packages'), 201);
    }

    public function updateLine(Request $request, int $id): JsonResponse
    {
        $line = Line::where('created_by', $this->reseller($request)->id)->findOrFail($id);

        $data = $request->validate([
            'username' => "sometimes|string|unique:lines,username,{$id}|max:255",
            'password' => 'sometimes|string|max:255',
            'exp_date' => 'nullable|date',
            'max_connections' => 'sometimes|integer|min:1|max:10',
            'is_trial' => 'boolean',
            'admin_enabled' => 'boolean',
            'bouquet' => 'nullable|array',
            'notes' => 'nullable|string',
            'package_ids' => 'nullable|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        if (isset($data['package_ids'])) {
            $line->packages()->sync($data['package_ids']);
            unset($data['package_ids']);
        }

        $line->update($data);
        return response()->json($line->fresh()->load('packages'));
    }

    public function deleteLine(Request $request, int $id): JsonResponse
    {
        Line::where('created_by', $this->reseller($request)->id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Users (sub-resellers) ────────────────────────────

    public function listUsers(Request $request): JsonResponse
    {
        return response()->json(
            User::where('id', '!=', $this->reseller($request)->id)
                ->where('member_group_id', $this->reseller($request)->member_group_id)
                ->orderByDesc('id')
                ->get(['id', 'username', 'email', 'created_at'])
        );
    }

    public function createUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|unique:users,username|max:255',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['member_group_id'] = $this->reseller($request)->member_group_id;
        $data['api_key'] = Str::random(48);

        return response()->json(User::create($data), 201);
    }

    // ─── Read-only ────────────────────────────────────────

    public function listPackages(): JsonResponse
    {
        return response()->json(Package::where('is_official', 1)->get(['id', 'package_name', 'is_trial']));
    }

    public function getServerInfo(): JsonResponse
    {
        $server = Server::where('is_main', 1)->first() ?? Server::where('status', 1)->first();
        if (!$server) {
            return response()->json(['error' => 'No server available'], 404);
        }
        return response()->json([
            'server_name' => $server->server_name,
            'url' => $server->domain_name ?? $server->server_ip,
            'port' => $server->http_port,
            'rtmp_port' => $server->rtmp_port,
            'status' => $server->status,
        ]);
    }
}
