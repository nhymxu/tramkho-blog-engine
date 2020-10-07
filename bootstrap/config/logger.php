<?php

// Logger settings
return [
    'name' => 'app',
    'path' => STORAGE_DIR . '/logs',
    'filename' => 'app.log',
    'level' => \Monolog\Logger::INFO,
    'file_permission' => 0775,
];
