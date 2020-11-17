<?php

namespace App\Action;

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

        return $this->render($response, 'tag_archive.twig', $viewData);
    }
}
