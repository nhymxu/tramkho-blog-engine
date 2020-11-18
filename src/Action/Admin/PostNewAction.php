<?php

namespace App\Action\Admin;

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
        $all_tags = $this->repository->getTags();

        $viewData = [
            'success'       => true,
            'post_action'   => 'new',
            'tags'          => [],
            'tag_ids'       => json_encode([]),
            'all_tags'      => $all_tags,
        ];

        return $this->render($response, '@admin/post.twig', $viewData);
    }
}
