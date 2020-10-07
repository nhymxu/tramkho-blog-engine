<?php

// Database settings
return [
    'dsn' => 'sqlite:' . STORAGE_DIR . '/database.sqlite',
    'username' => null,
    'password' => null,
    'flags' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => false,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements. Disable emulation of prepared statements, use REAL prepared statements instead
        PDO::ATTR_EMULATE_PREPARES => false, // mysql true
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
];
