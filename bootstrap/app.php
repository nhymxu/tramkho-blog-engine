<?php
use DI\ContainerBuilder;
use Nhymxu\BasePathDetector;
use Slim\App;

defined('ROOT_DIR') || define('ROOT_DIR', dirname(__DIR__));
defined('STORAGE_DIR') || define('STORAGE_DIR', ROOT_DIR . '/storage');
defined('TEMPLATE_DIR') || define('TEMPLATE_DIR', STORAGE_DIR . '/templates');
defined('TMP_DIR') || define('TMP_DIR', STORAGE_DIR . '/tmp');
defined('BOOTSTRAP_DIR') || define('BOOTSTRAP_DIR', ROOT_DIR . '/bootstrap');

require_once ROOT_DIR . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// Add container definitions
$containerBuilder->addDefinitions(BOOTSTRAP_DIR . '/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Create App instance
$app = $container->get(App::class);

$basePathDetector = new BasePathDetector($_SERVER);
$app->setBasePath($basePathDetector->getBasePath());

// Register routes
(require BOOTSTRAP_DIR . '/routes.php')($app);

// Register middleware
(require BOOTSTRAP_DIR . '/middleware.php')($app);

return $app;
