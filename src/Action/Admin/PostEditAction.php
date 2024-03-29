<?php

namespace App\Action\Admin;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class PostEditAction extends BaseAction
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
        $post_id = (int)$args['post-id'];

        $post = $this->repository->getById($post_id);
        $tags = $this->repository->getPostTag($post_id);
        $all_tags = $this->repository->getTags();

        $query_params = $request->getQueryParams();
        $notice = $query_params['notice'] ?? '';

        $tag_ids = [];
        foreach ($tags as $tag) {
            $tag_ids[] = (int) $tag['id'];
        }

        $viewData = [
            'success'       => true,
            'notice'        => $notice,
            'post_action'   => 'update',
            'post'          => $post,
            'tags'          => $tags,
            'tag_ids'       => json_encode($tag_ids),
            'all_tags'      => $all_tags,
        ];

        return $this->render($response, '@admin/post.twig', $viewData);
    }
}
