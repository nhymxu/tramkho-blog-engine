<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;

defined('ROOT_DIR') || define('ROOT_DIR', dirname(__DIR__));
defined('BOOTSTRAP_DIR') || define('BOOTSTRAP_DIR', ROOT_DIR . '/bootstrap');

require_once ROOT_DIR . '/vendor/autoload.php';

$env = (new ArgvInput())->getParameterOption(['--env', '-e'], 'development');

if ($env) {
    $_ENV['APP_ENV'] = $env;
}

/** @var ContainerInterface $container */
$container = (require BOOTSTRAP_DIR . '/app.php')->getContainer();

$application = new Application();

$application->getDefinition()->addOption(
    new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'development')
);

foreach ($container->get('settings')['commands'] as $class) {
    $application->add($container->get($class));
}

$application->run();
