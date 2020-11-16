<?php

use App\ErrorHandler;
use App\Markdown\MarkdownConverter;
use Nhymxu\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\Twig;
use Tuupola\Middleware\HttpBasicAuthentication;
use Twig\Extra\Markdown\{MarkdownExtension, MarkdownRuntime};
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

return [
    'settings' => static function () {
        return require BOOTSTRAP_DIR . '/settings.php';
    },

    App::class => static function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },

    // For the responder
    ResponseFactoryInterface::class => static function (ContainerInterface $container): ResponseFactoryInterface {
        return $container->get(App::class)->getResponseFactory();
    },

    // The Slim RouterParser
    RouteParserInterface::class => static function (ContainerInterface $container): RouteParserInterface {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    },

    // Twig templates
    Twig::class => static function (ContainerInterface $container) {
        $config = (array)$container->get('settings');
        $settings = $config['twig'];

        $options = $settings['options'];
        $options['cache'] = $options['cache_enabled'] ? $options['cache_path'] : false;

        $twig = Twig::create($settings['paths'], $options);

        $loader = $twig->getLoader();
        $publicPath = (string)$config['public'];
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath($publicPath, 'public');
        }

        // Add extensions
        $twig->addExtension(new MarkdownExtension());
        $twig->addExtension(new StringExtension());

        $environment = $twig->getEnvironment();
        $environment->addRuntimeLoader(new class implements RuntimeLoaderInterface {
            public function load(string $class)
            {
                if (MarkdownRuntime::class === $class) {
                    return new MarkdownRuntime(new MarkdownConverter());
                }

                return null;
            }
        });

        $environment->addGlobal('appConfig', $config['app_config'] ?? []);

        return $twig;
    },

    PDO::class => static function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];

        $dsn = $settings['dsn'];
        $username = $settings['username'];
        $password = $settings['password'];
        $flags = $settings['flags'];

        return new PDO($dsn, $username, $password, $flags);
    },

    // The logger factory
    LoggerFactory::class => static function (ContainerInterface $container) {
        return new LoggerFactory($container->get('settings')['logger']);
    },

    ErrorMiddleware::class => static function (ContainerInterface $container) {
        $config = $container->get('settings')['error'];
        $app = $container->get(App::class);

        $logger = $container->get(LoggerFactory::class)
            ->addFileHandler('error.log')
            ->createInstance('default_error_handler');

        $errorMiddleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$config['display_error_details'],
            (bool)$config['log_errors'],
            (bool)$config['log_error_details'],
            $logger
        );

        $errorMiddleware->setDefaultErrorHandler($container->get(ErrorHandler::class));

        return $errorMiddleware;
    },

    HttpBasicAuthentication::class => static function (ContainerInterface $container) {
        $settings = $container->get('settings')['auth'];
        $app = $container->get(App::class);
        $base_path = $app->getBasePath();

        $protected_path = $base_path . $settings['path'];

        // Prevent forgot setup authentication
        if (empty($settings['username'])) {
            $settings['username'] = 'root';
        }

        if (empty($settings['password'])) {
            $settings['password'] = base64_encode(random_bytes(10));
        }

        return new HttpBasicAuthentication([
            "path" => $protected_path,
            "realm" => "Protected",
            "relaxed" => $settings['whitelist'],
            "users" => [
                $settings['username'] => $settings['password'],
            ],
            "error" => function ($response, $arguments) {
                $data = [];
                $data["status"] = "error";
                $data["message"] = $arguments["message"];

                $body = $response->getBody();
                $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));

                return $response->withBody($body);
            }
        ]);
    },
];
