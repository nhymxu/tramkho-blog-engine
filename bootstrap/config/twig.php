<?php

// Twig settings
// Configuration reference: https://symfony.com/doc/current/reference/configuration/twig.html
return [
    'paths' => [
        TEMPLATE_DIR . '/simple',
        'admin' => TEMPLATE_DIR . '/admin',
    ],
    'options' => [
        'debug' => false,
        'cache_enabled' => true,
        'cache_path' => TMP_DIR . '/twig',
    ],
];
