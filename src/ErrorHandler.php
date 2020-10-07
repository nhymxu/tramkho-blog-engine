<?php

namespace App;

use Nhymxu\DefaultErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ErrorHandler extends DefaultErrorHandler
{
    /**
     * Process rendered output
     *
     * @param Throwable $exception
     * @param bool $displayErrorDetails
     * @return ResponseInterface
     */
    public function process(Throwable $exception, bool $displayErrorDetails): ResponseInterface
    {
        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'text/html; charset=utf-8');

        // Detect status code
        $statusCode = $this->getHttpStatusCode($exception);

        if ($exception instanceof PostNotFoundException) {
            $this->fileRender($response, 'errors/404-lost-book.html');
            return $response->withStatus($statusCode);
        }

        return parent::process($exception, $displayErrorDetails);
    }

    /**
     * Get http status code.
     *
     * @param Throwable $exception The exception
     *
     * @return int The http code
     */
    protected function getHttpStatusCode(Throwable $exception): int
    {
        if ($exception instanceof PostNotFoundException) {
            return 404;
        }

        return parent::getHttpStatusCode($exception);
    }

    private function fileRender(ResponseInterface $response, string $file): void
    {
        $file_path = $this->container->get('settings')['template_dir'] . '/' . $file;

        $file_content = file_get_contents($file_path);
        if (!$file_content) {
            $file_content = '';
        }

        $response->getBody()->write($file_content);
    }
}
