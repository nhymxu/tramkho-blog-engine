<?php
namespace App\Action\Admin;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;


/**
 * Action.
 */
final class PostSaveAction extends BaseAction
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
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $data = (array)$request->getParsedBody();

        $now = date('Y-m-d H:i:s');

        $payload = [
            'slug'          => $data['post_slug'],
            'title'         => $data['post_title'],
            'content'       => $data['post_content'],
            'status'        => $data['post_status'],
            'update_time'   => $now,
        ];

        if ($data['post_action'] === 'new') {
            $payload['create_time'] = $now;
            $post_id = (int)$this->repository->createPost($payload);
        } else {
            $payload['post_id'] = $data['post_id'];
            $this->repository->updatePost($payload);
            $post_id = (int)$payload['post_id'];
        }

        if ($data['post_status'] === 'publish') {
            $this->repository->updatePostPublishTime($post_id, $now);
        }

        $tags = $this->repository->getPostTag($post_id);
        $tag_ids = [];
        foreach ($tags as $tag) {
            $tag_ids[] = (int) $tag['id'];
        }

        $this->assignPostTag($post_id, (array)$data['post_tag'], $tag_ids);


        $url = $routeParser->urlFor(
            'admin:post_edit',
            ['post-id' => (string)$post_id],
            ['notice' => 'Saved']
        );

        return $response->withHeader('Location', $url)
                        ->withStatus(302);
    }

    private function assignPostTag(int $post_id, array $tags, array $current_tags): void
    {
        $delete_tags = array_diff($current_tags, $tags);
        foreach($delete_tags as $tag_id) {
            $this->repository->removePostTag($post_id, (int)$tag_id);
        }

        foreach($tags as $tag) {
            $tag_id = $tag;

            if (strpos($tag, "+") === 0) {
                $tag_name = ltrim($tag, '+');
                $tag_slug = $this->sanitize($tag_name);
                $tag_id = $this->repository->createTag($tag_name, $tag_slug);
            }

            if (!in_array($tag_id, $current_tags)) {
                $this->repository->updatePostTag($post_id, (int)$tag_id);
            }
        }
    }

    private function sanitize(string $title): string
    {
        $replacement = '-';
        $map = array();
        $quotedReplacement = preg_quote($replacement, '/');
        $default = array(
            '/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ|å/' => 'a',
            '/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ|ë/' => 'e',
            '/ì|í|ị|ỉ|ĩ|Ì|Í|Ị|Ỉ|Ĩ|î/' => 'i',
            '/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ|ø/' => 'o',
            '/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ|ů|û/' => 'u',
            '/ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ/' => 'y',
            '/đ|Đ/' => 'd',
            '/ç/' => 'c',
            '/ñ/' => 'n',
            '/ä|æ/' => 'ae',
            '/ö/' => 'oe',
            '/ü/' => 'ue',
            '/Ä/' => 'Ae',
            '/Ü/' => 'Ue',
            '/Ö/' => 'Oe',
            '/ß/' => 'ss',
            '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/\\s+/' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        );
        //Some URL was encode, decode first
        $title = urldecode($title);
        $map = array_merge($map, $default);
        return strtolower((string)preg_replace(array_keys($map), array_values($map), $title));
    }
}
