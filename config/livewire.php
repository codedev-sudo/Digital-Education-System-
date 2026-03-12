<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Here you may configure how Livewire stores temporary uploaded files
    | before they are persisted. We explicitly set the disk to `public`
    | so Filament file uploads can reliably read file metadata (like size)
    | from `storage/app/public/livewire-tmp`.
    |
    */

    'temporary_file_upload' => [
        'disk' => 'local',
        'directory' => 'livewire-tmp',
        'rules' => null,
        'middleware' => null,
        'preview_mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ],
    ],
];

