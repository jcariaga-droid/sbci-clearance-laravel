
<?php

use Illuminate\Support\Str;

return [

    'driver' => env('SESSION_DRIVER', 'file'),

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => 'sbci-clearance-session',

    'path' => '/',

    'domain' => null,

    'secure' => true,

    'http_only' => true,

    'same_site' => 'lax',

    'partitioned' => false,

];
