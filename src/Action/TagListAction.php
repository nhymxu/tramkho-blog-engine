<?php

namespace App\Action;

use App\Domain\Blog\BlogRepository;
use Nhymxu\Responder;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class TagListAction extends BaseAction
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
        $tags = $this->blogRepository->getTagCloud();

        $viewData = [
            'success' => true,
            'tags'  => $tags,
        ];

        return $this->responder->render($response, 'tag_archive.twig', $viewData);
    }
}
