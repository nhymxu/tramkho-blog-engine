<?php

namespace Nhymxu\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\StreamFactory;
use voku\helper\HtmlMin;

final class HtmlMinifierMiddleware implements MiddlewareInterface
{
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
        $response = $handler->handle($request);

        if (NX_DEBUG !== true && stripos($response->getHeaderLine('Content-Type'), 'text/html') === 0)
        {
            // $response->getBody()->getContents()
            $compressedBody = $this->minify((string) $response->getBody());

            $stream = (new StreamFactory)->createStream($compressedBody);

            return $response->withBody($stream)->withoutHeader('Content-Length');
        }

        return $response;
    }

    private function minify(string $content): string
    {
        $htmlMin = new HtmlMin();
        return $htmlMin->minify($content);
    }
}
