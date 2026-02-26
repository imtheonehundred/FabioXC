<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Outbound identity when fetching external stream URLs
    |--------------------------------------------------------------------------
    |
    | When the server restreams from external M3U/stream URLs (e.g. FFmpeg
    | pulling an http(s) source), these settings are used so upstream
    | providers see a normal end-user client instead of a server/restream tool.
    | Many providers block typical server User-Agents (Lavf/FFmpeg, VLC, etc.).
    | Set outbound_user_agent in .env (STREAMING_OUTBOUND_USER_AGENT) or here.
    | Add optional headers (e.g. Accept, Referer) in outbound_headers if your
    | upstream requires them.
    |
    */

    'outbound_user_agent' => env('STREAMING_OUTBOUND_USER_AGENT', 'IPTV/1.0 (compatible; set-top player)'),

    'outbound_headers' => [
        // e.g. 'Accept: */*', 'Referer: https://provider.example.com/'
    ],

];
