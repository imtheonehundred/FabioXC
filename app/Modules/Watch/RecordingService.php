<?php

namespace App\Modules\Watch;

use Illuminate\Support\Facades\Log;

class RecordingService
{
    public function onStreamStarted(mixed $payload): void
    {
        Log::info('Watch module: stream started', ['stream_id' => $payload['stream_id'] ?? null]);
    }
}
