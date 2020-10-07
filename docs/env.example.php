<?php

$settings['error']['display_error_details'] = true;
$settings['error']['log_errors'] = true;
$settings['error']['log_error_details'] = true;

$settings['logger']['level'] = \Monolog\Logger::DEBUG;

$settings['twig']['options']['debug'] = true;
$settings['twig']['options']['cache_enabled'] = false;

// ignore post have tag_id listed on homepage
$settings['homepage']['ignore']['tag'] = [1];

// inject google tag manager script
$settings['app_config']['gtm'] = 'GTM-KQR7GC';
