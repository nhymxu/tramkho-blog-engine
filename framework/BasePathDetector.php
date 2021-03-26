<?php

namespace Nhymxu;

use Selective\BasePath\BasePathDetector as SelectiveBasePathDetector;

class BasePathDetector
{
    /**
     * @var array The server data
     */
    private $server;

    /**
     * @var string The PHP_SAPI value
     */
    private $phpSapi;

    /**
     * The constructor.
     *
     * @param array $server The SERVER data to use
     * @param string|null $phpSapi The PHP_SAPI value
     */
    public function __construct(array $server = [], string $phpSapi = null)
    {
        $this->server = $server;
        $this->phpSapi = $phpSapi ?? PHP_SAPI;
    }

    public function getBasePath(): string
    {
        // For nginx
        if ($this->phpSapi === 'fpm-fcgi') {
            return $this->getBasePathFromNginx($this->server);
        }

        $selectiveDetector = new SelectiveBasePathDetector($this->server, $this->phpSapi);
        return $selectiveDetector->getBasePath();
    }

    /**
     * Return basePath for nginx server.
     * https://github.com/oscarotero/psr7-middlewares/blob/master/src/Middleware/BasePath.php
     *
     * @param array $server The SERVER data to use
     *
     * @return string The base path
     */
    private function getBasePathFromNginx(array $server): string
    {
        if (!isset($server['SCRIPT_FILENAME'])) {
            return '';
        }

        $scriptFileName = $server['SCRIPT_FILENAME'];
        $phpSelf = $server['PHP_SELF'] ?? null;
        $requestUri = $server['REQUEST_URI'];

        $baseUrl = '/';
        $baseName = basename($scriptFileName);

        if ($baseName) {
            $path = ($phpSelf ? trim($phpSelf, '/') : '');
            $basePos = strpos($path, $baseName) ?: 0;
            $baseUrl .= substr($path, 0, $basePos) . $baseName;
        }

        // If the baseUrl is empty, then simply return it.
        if (empty($baseUrl)) {
            return '';
        }

        // Directory portion of base path matches.
        $baseDir = str_replace('\\', '/', dirname($baseUrl));

        if ($baseDir === '/') {
            return '';
        }

        if (0 === strpos($requestUri, $baseDir)) {
            return $baseDir;
        }

        return '';
    }
}
