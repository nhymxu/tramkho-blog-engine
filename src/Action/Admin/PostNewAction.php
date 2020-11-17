<?php

namespace App\Action\Admin;

use App\Action\BaseAction;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class PostNewAction extends BaseAction
{
    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args The route argument list
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $viewData = [
            'success' => true,
            'post_action' => 'new',
        ];

        return $this->render($response, '@admin/post.twig', $viewData);
    }
}
