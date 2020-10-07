<?php
namespace App\Markdown;

use League\CommonMark\Environment;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Twig\Extra\Markdown\MarkdownInterface;

class MarkdownConverter implements MarkdownInterface
{
    /**
     * @var GithubFlavoredMarkdownConverter
     */
    private $converter;

    public function __construct()
    {
        $config = [];

        $environment = Environment::createGFMEnvironment();
        $environment->addInlineParser(new NewlineParser(), 200);

        $this->converter = new GithubFlavoredMarkdownConverter($config, $environment);
    }

    public function convert(string $body): string
    {
        return $this->converter->convertToHtml($body);
    }
}
