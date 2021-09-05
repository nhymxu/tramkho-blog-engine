<?php

namespace App\Action;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class HomeAction extends BaseAction
{
    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $filter = [];
        $ignore_tag = $this->container->get('settings')['homepage']['ignore']['tag'] ?? false;
        if ($ignore_tag) {
            $filter['ignore_tag'] = $ignore_tag;
        }

        $uriData = [
            'type'  => 'home',
            'query_params' => $request->getQueryParams(),
            'data'  => []
        ];

        $post_per_page = $this->container->get('settings')['homepage']['post_per_page'] ?? 20;
        $viewData = $this->get_pagination_posts($request, $filter, $uriData, $post_per_page);

        $is_get_post_tag = $this->container->get('settings')['homepage']['get_post_tag'] ?? false;
        if ($is_get_post_tag) {
            $viewData['posts'] = $this->get_posts_tags($viewData['posts']);
        }

        return $this->render($response, 'archive.twig', $viewData);
    }
}
