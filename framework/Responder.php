<?php

namespace Nhymxu;

use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

/**
 * A generic responder.
 */
final class Responder
{
    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * The constructor.
     *
     * @param Twig $twig The twig engine
     * @param UrlGenerator $urlGenerator The url generator
     * @param ResponseFactoryInterface $responseFactory The response factory
     */
    public function __construct(
        Twig $twig,
        UrlGenerator $urlGenerator,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Create a new response.
     *
     * @return ResponseInterface The response
     */
    public function createResponse(): ResponseInterface
    {
        return $this
            ->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Output rendered template.
     *
     * @param ResponseInterface $response The response
     * @param string $template Template pathname relative to templates directory
     * @param array $data Associative array of template variables
     *
     * @return ResponseInterface The response
     */
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        return $this->twig->render($response, $template, $data);
    }

    /**
     * Check template file exists or not
     *
     * @param string $template_name
     * @return bool
     */
    public function template_exists(string $template_name): bool
    {
        return $this->twig->getLoader()->exists($template_name);
    }

    /**
     * Creates a redirect for the given url / route name.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $destination The redirect destination (url or route name)
     * @param array<mixed> $data Named argument replacement data
     * @param array<mixed> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function redirect(
        ResponseInterface $response,
        string $destination,
        array $data = [],
        array $queryParams = []
    ): ResponseInterface {
        if (!filter_var($destination, FILTER_VALIDATE_URL)) {
            $destination = $this->urlGenerator->fullUrlFor($destination, $data, $queryParams);
        }

        return $response->withStatus(302)->withHeader('Location', $destination);
    }

    /**
     * Write JSON to the response body.
     *
     * This method prepares the response object to return an HTTP JSON
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param mixed $data The data
     * @param int $options Json encoding options
     *
     * @throws JsonException
     *
     * @return ResponseInterface The response
     */
    public function json(
        ResponseInterface $response,
        $data = null,
        int $options = 0
    ): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write((string)json_encode($data, JSON_THROW_ON_ERROR | $options));

        return $response;
    }
}
