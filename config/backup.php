<?php

return [
    // Windows example: 'C:\\xampp\\mysql\\bin\\mysqldump.exe'
    'dump_binary'   => env('DB_DUMP_BINARY', 'mysqldump'),
    'client_binary' => env('DB_CLIENT_BINARY', 'mysql'),
    'directory'     => 'backups',
    'max_upload_kb' => 512000, // 500 MB
];