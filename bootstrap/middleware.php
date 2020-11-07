<?php

use Nhymxu\Middleware\{TrailingSlash, UrlGeneratorMiddleware};
use Slim\App;
use Slim\Views\{TwigMiddleware, Twig};
use Slim\Middleware\ErrorMiddleware;
use Tuupola\Middleware\HttpBasicAuthentication;

return static function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    $app->add(UrlGeneratorMiddleware::class);

    $app->add(HttpBasicAuthentication::class);

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    $app->add(new TrailingSlash($app));

    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

    // Catch exceptions and errors
    $app->add(ErrorMiddleware::class);
};
