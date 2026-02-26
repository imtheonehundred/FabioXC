<?php

namespace App\Modules\Ministra;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $type = $request->input('type', 'stb');
        $action = $request->input('action', 'handshake');

        return response()->json([
            'js' => [],
            'status' => 'ok',
            'type' => $type,
            'action' => $action,
            'module' => 'ministra',
        ]);
    }
}
