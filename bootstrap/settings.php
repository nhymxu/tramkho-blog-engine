<?php

// Error reporting
error_reporting(0);
ini_set('display_errors', '0');

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

$settings = [
    'public_dir'    => ROOT_DIR . '/public',
];

$settings['db'] = require BOOTSTRAP_DIR . '/config/database.php';
$settings['logger'] = require BOOTSTRAP_DIR . '/config/logger.php';
$settings['twig'] = require BOOTSTRAP_DIR . '/config/twig.php';
$settings['commands'] = require BOOTSTRAP_DIR . '/config/command.php';

if (file_exists(ROOT_DIR . '/.env.php')) {
    require ROOT_DIR . '/.env.php';
}

return $settings;
