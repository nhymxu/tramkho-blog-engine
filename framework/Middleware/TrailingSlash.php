<?php


namespace Nhymxu\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Psr7\Response;

/**
 * Middleware.
 */
final class TrailingSlash implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $basePath;

    public function __construct(App $app)
    {
        $this->basePath = $app->getBasePath();
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
        $uri = $request->getUri();
        $path = $uri->getPath();

        // recursively remove slashes when its more than 1 slash
        $rPath = rtrim($path, '/');

        if ($path !== '/' && $rPath !== $this->basePath && substr($path, -1) === '/') {
            // permanently redirect paths with a trailing slash
            // to their non-trailing counterpart
            $uri = $uri->withPath($rPath);
            if ($request->getMethod() === 'GET') {
                $response = new Response();
                return $response
                    ->withHeader('Location', (string) $uri)
                    ->withStatus(301);
            }

            $request = $request->withUri($uri);
        }
        return $handler->handle($request);
    }
}
