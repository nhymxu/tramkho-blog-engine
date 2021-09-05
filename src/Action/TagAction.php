<?php

namespace App\Action;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class TagAction extends BaseAction
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
        $slug = $args['tag-slug'];

        $query_params = $request->getQueryParams();
        unset($query_params['/tag/' . $slug]);

        $tag = $this->blogRepository->getTag($slug);

        $filter = ['tag_id' => $tag['id']];

        $uriData = [
            'type'  => 'tag',
            'query_params' => $query_params,
            'data'  => ['tag-slug' => $slug]
        ];

        $viewData = $this->get_pagination_posts($request, $filter, $uriData);
        $viewData['page_title'] = 'Tag: ' . $tag['name'] . $viewData['page_title'];
        $viewData['tag_name'] = $tag['name'];

        $is_get_post_tag = $this->container->get('settings')['homepage']['get_post_tag'] ?? false;
        if ($is_get_post_tag) {
            $viewData['posts'] = $this->get_posts_tags($viewData['posts']);
        }

        $template = 'archive.twig';
        if ($this->responder->template_exists('tag.twig')) {
            $template = 'tag.twig';
        }

        return $this->render($response, $template, $viewData);
    }
}
