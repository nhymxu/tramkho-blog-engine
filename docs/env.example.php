<?php

// enable this line to stop minify html output
// require __DIR__ . '/docs/env.debug.php';

// ignore post have tag_id listed on homepage
$settings['homepage']['ignore']['tag'] = [1];

// get post tag on post list or not
$settings['homepage']['get_post_tag'] = false;

// config number of post per page. If not set, default is 20
$settings['homepage']['post_per_page'] = 10;

// inject google tag manager script
$settings['app_config']['gtm'] = 'GTM-KQR7GC';

// setup domain for sitemap
$settings['app_config']['url'] = 'http://localhost/blog';

// custom theme
$settings['app_config']['theme'] = 'simple';

/**
 * authentication
 */

// Protected path
$settings['auth']['path'] = '/admin';

// Auth info
$settings['auth']['username'] = 'root';
$settings['auth']['password'] = 'root';

// https whitelist
$settings['auth']['whitelist'] = [
    'localhost',
    'dungnt.net',
];
