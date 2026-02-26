<?php

/**
 * Error codes for API and streaming responses.
 * Used by Player API, Internal API, and streaming delivery for consistent client handling.
 */

return [
    // Auth / line
    100 => 'Invalid username or password',
    101 => 'Line expired',
    102 => 'Line disabled',
    103 => 'Max connections exceeded',
    104 => 'Line not found',
    105 => 'Invalid token or HMAC',

    // Stream / VOD
    200 => 'Stream not found',
    201 => 'Stream disabled',
    202 => 'Stream offline',
    203 => 'Access denied to this stream',
    204 => 'VOD not found',
    205 => 'VOD disabled',
    206 => 'Episode not found',

    // Geo / blocklist
    300 => 'Access denied: region blocked',
    301 => 'IP blocked',
    302 => 'ISP blocked',
    303 => 'User-Agent blocked',

    // Server / internal
    400 => 'Server error',
    401 => 'Service temporarily unavailable',
    402 => 'Invalid request',
    403 => 'Forbidden',

    // Rate limit
    429 => 'Too many requests',
];
