<?php

namespace App\Action;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class PingAction
{
    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()->write('pong');
        return $response;
    }
}
