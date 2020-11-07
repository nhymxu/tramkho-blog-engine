<?php
namespace App\Action\Admin;

use App\Domain\Blog\AdminRepository;
use Nhymxu\Responder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;


/**
 * Action.
 */
final class PostTrashAction extends BaseAction
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
        $data = $request->getParsedBody();

        $now = date('Y-m-d H:i:s');

        $payload = [
            'post_id'       => $data['post_id'],
            'status'        => 'trash',
            'update_time'   => $now,
        ];

        $this->repository->updatePostStatus($payload);

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor(
            'admin:post_list',
            [],
            ['notice' => 'Post ID '. $data['post_id'] . ' moved to trash.']
        );

        return $response->withHeader('Location', $url)
            ->withStatus(302);
    }
}
