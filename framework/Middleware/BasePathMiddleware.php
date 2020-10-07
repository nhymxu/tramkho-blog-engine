<?php


namespace Nhymxu\Middleware;

use Nhymxu\BasePathDetector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;

/**
 * Slim 4 Base path middleware.
 */
final class BasePathMiddleware implements MiddlewareInterface
{
    /**
     * @var App The slim app
     */
    private $app;

    /**
     * The constructor.
     *
     * @param App $app The slim app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $detector = new BasePathDetector($request->getServerParams());

        $this->app->setBasePath($detector->getBasePath());

        return $handler->handle($request);
    }
}
