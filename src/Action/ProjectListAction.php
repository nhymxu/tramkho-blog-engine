<?php

namespace App\Action;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Slim\Routing\RouteContext;

final class ProjectListAction extends BaseAction
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
        $data_files = STORAGE_DIR . '/projects.json';
        $template_file = 'project_archive.twig';

        if (!file_exists($data_files) || !$this->responder->template_exists($template_file)) {
            return $this->_return_home($request, $response);
        }

        try {
            $data = file_get_contents($data_files);
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            return $this->_return_home($request, $response);
        }

        if (empty($data)) {
            return $this->_return_home($request, $response);
        }

        return $this->render($response, $template_file, ['projects' => $data]);
    }

    private function _return_home($request, $response)
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('home');

        return $response->withHeader('Location', $url)
            ->withStatus(302);
    }
}
