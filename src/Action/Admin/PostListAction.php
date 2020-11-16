<?php
namespace App\Action\Admin;

use App\Action\BaseAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class PostListAction extends BaseAction
{
    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array<string> $args The arguments
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $query_params = $request->getQueryParams();
        unset($query_params['/admin/posts'], $query_params['notice']);

        $filter = [
            'status' => '',
        ];

        if (isset($query_params['status']) && trim($query_params['status']) !== '') {
            $filter['status'] = $query_params['status'];
        }

        $uriData = [
            'type'  => 'admin:post_list',
            'query_params' => $query_params,
            'data'  => []
        ];

        $viewData = $this->get_pagination_posts($request, $filter, $uriData, 15);

        return $this->responder->render($response, '@admin/post_list.twig', $viewData);
    }
}
