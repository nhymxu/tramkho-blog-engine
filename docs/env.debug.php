<?php

define('NX_DEBUG', true);

$settings['error']['display_error_details'] = true;
$settings['error']['log_errors'] = true;
$settings['error']['log_error_details'] = true;

$settings['logger']['level'] = \Monolog\Logger::DEBUG;

$settings['twig']['options']['debug'] = true;
$settings['twig']['options']['cache_enabled'] = false;
