<?php
namespace App\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter as LeagueMarkdownConverter;
use Twig\Extra\Markdown\MarkdownInterface;

class MarkdownConverter implements MarkdownInterface
{
    /**
     * @var LeagueMarkdownConverter
     */
    private $converter;

    public function __construct()
    {
        $config = [];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $environment->addInlineParser(new NewlineParser(), 200);

        $this->converter = new LeagueMarkdownConverter($environment);
    }

    public function convert(string $body): string
    {
        return $this->converter->convertToHtml($body);
    }
}
