<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

defined('ROOT_DIR') || define('ROOT_DIR', dirname(__DIR__));
defined('BOOTSTRAP_DIR') || define('BOOTSTRAP_DIR', ROOT_DIR . '/bootstrap');

require_once ROOT_DIR . '/vendor/autoload.php';

$env = (new ArgvInput())->getParameterOption(['--env', '-e'], 'dev');

if ($env) {
    $_ENV['APP_ENV'] = $env;
}

/** @var ContainerInterface $container */
$container = (require BOOTSTRAP_DIR . '/app.php')->getContainer();

try {
    $application = $container->get(Application::class);
    exit($application->run());
} catch (Throwable $exception) {
    echo $exception->getMessage();
    exit(1);
}
