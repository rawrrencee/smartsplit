<?php

return [

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'private',
        ],
    ],

];
