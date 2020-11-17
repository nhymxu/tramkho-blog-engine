<?php

namespace App\Action;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class PostAction extends BaseAction
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
        $slug = $args['post-slug'];

        $post = $this->blogRepository->getBySlug($slug);
        $tags = $this->blogRepository->getPostTag($post['id']);

        $viewData = [
            'success' => true,
            'slug' => $slug,
            'post' => $post,
            'tags'  => $tags,
        ];

        return $this->render($response, 'post.twig', $viewData);
    }
}
