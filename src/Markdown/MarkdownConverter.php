<?php
namespace App\Markdown;

use Embed\Embed;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\Embed\Bridge\OscaroteroEmbedAdapter;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter as LeagueMarkdownConverter;
use Twig\Extra\Markdown\MarkdownInterface;

class MarkdownConverter implements MarkdownInterface
{
    /**
     * @var LeagueMarkdownConverter
     */
    private LeagueMarkdownConverter $converter;

    public function __construct()
    {
        $embedLibrary = new Embed();
        $embedLibrary->setSettings([
            'oembed:query_parameters' => [
                'maxwidth' => 800,
                'maxheight' => 600,
            ],
        ]);

        $config = [
            'embed' => [
                'adapter' => new OscaroteroEmbedAdapter($embedLibrary),
                'allowed_domains' => ['youtube.com'],
                'fallback' => 'link',
            ],
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $environment->addExtension(new EmbedExtension());

        $environment->addInlineParser(new NewlineParser(), 200);

        $this->converter = new LeagueMarkdownConverter($environment);
    }

    public function convert(string $body): string
    {
        return $this->converter->convert($body);
    }
}
