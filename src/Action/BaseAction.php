<?php

namespace App\Action;

use Nhymxu\Responder;
use App\Domain\Blog\BlogRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;

/**
 * BaseAction
 */
class BaseAction
{
    /**
     * @var Responder
     */
    protected $responder;

    /**
     * @var BlogRepository
     */
    protected $blogRepository;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * PostAction constructor.
     *
     * @param Responder $responder The responder
     * @param BlogRepository $blogRepository
     * @param ContainerInterface $container
     */
    public function __construct(Responder $responder, BlogRepository $blogRepository, ContainerInterface $container)
    {
        $this->responder = $responder;
        $this->blogRepository = $blogRepository;
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $filter
     * @param array $uriData
     * @param int $perPage
     * @return array
     */
    protected function get_pagination_posts(
        ServerRequestInterface $request,
        array $filter,
        array $uriData,
        int $perPage = 20
    ): array
    {
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $current_page = $this->get_page($uriData['query_params']);

        $posts = $this->blogRepository->getByPage($current_page, $filter, $perPage);
        $pagination = $this->blogRepository->getPagination($current_page, $filter, $perPage);

        if ($pagination['prev'] !== '') {
            $prev = $uriData['query_params'];
            $prev['page'] = $pagination['prev'];
            $pagination['prev'] = $routeParser->urlFor($uriData['type'], $uriData['data'], $prev);
        }

        if ($pagination['next'] !== '') {
            $next = $uriData['query_params'];
            $next['page'] = $pagination['next'];
            $pagination['next'] = $routeParser->urlFor($uriData['type'], $uriData['data'], $next);
        }

        $base = $uriData['query_params'];
        unset($base['page']);
        if (empty($base) && !isset($base['status'])) {
            $base['status'] = '';
        }

        $pagination['base'] = $routeParser->urlFor($uriData['type'], $uriData['data'], $base);

        return [
            'current_page'  => $current_page,
            'posts'         => $posts,
            'page_nav'      => $pagination,
            'page_title'    => 'Page: ' . $current_page,
        ];
    }

    /**
     * Get current page number
     *
     * @param array $query_params
     * @return int
     */
    private function get_page(array $query_params): int
    {
        $page = 1;
        if (isset($query_params['page']) && trim($query_params['page']) !== '') {
            $page = (int)$query_params['page'];
        }

        if ($page < 1) {
            $page = 1;
        }

        return $page;
    }

    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        return $this->responder
            ->render($response, $template, $data)
            ->withHeader('Content-Type', 'text/html');
    }

    /**
     * Get post tags for each post on list
     *
     * @param array $post_list
     * @return array
     */
    protected function get_posts_tags(array $post_list)
    {
        if (empty($post_list)) {
            return $post_list;
        }

        $posts = [];
        $post_ids = [];

        foreach($post_list as $post) {
            $post['tags'] = [];
            $posts[$post['id']] = $post;
            $post_ids[] = $post['id'];
        }

        $results = $this->blogRepository->getPostTagList($post_ids);

        foreach($results as $row) {
            $posts[$row['post_id']]['tags'][] = $row;
        }

        return $posts;
    }
}
