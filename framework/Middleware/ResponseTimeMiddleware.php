<?php
declare(strict_types = 1);

namespace Nhymxu\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ResponseTimeMiddleware implements MiddlewareInterface
{
    private const HEADER = 'X-Response-Time';

    /**
     * Process a server request and return a response.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $server = $request->getServerParams();
        $startTime = $server['REQUEST_TIME_FLOAT'] ?? microtime(true);
        $response = $handler->handle($request);

        return $response->withHeader(self::HEADER, sprintf('%2.3fms', (microtime(true) - $startTime) * 1000));
    }
}
