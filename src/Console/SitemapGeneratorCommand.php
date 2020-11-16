<?php

namespace App\Console;

use App\Domain\Blog\BlogRepository;
use Icamys\SitemapGenerator\SitemapGenerator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command.
 */
final class SitemapGeneratorCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var BlogRepository
     */
    private $blogRepository;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The container
     * @param BlogRepository $blogRepository
     * @param string|null $name The name
     */
    public function __construct(ContainerInterface $container, BlogRepository $blogRepository,?string $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
        $this->blogRepository = $blogRepository;
    }

    /**
     * Configure.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('sitemap-generator');
        $this->setDescription('Generate a sitemap.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input The input
     * @param OutputInterface $output The output
     *
     * @return int The error code, 0 on success
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $settings = $this->container->get('settings');

        if (!isset($settings['app_config']['url']) || $settings['app_config']['url'] === '') {
            throw new \RuntimeException("url not found.");
        }

        $home_url = $settings['app_config']['url'];
        if (substr($home_url, -1) !== '/') {
            $home_url .= '/';
        }

        $generator = new SitemapGenerator($home_url, $settings['public_dir']);
        $generator->toggleGZipFileCreation();
        $generator->setMaxURLsPerSitemap(50000);
        $generator->setSitemapFileName("sitemap.xml");
        $generator->setSitemapIndexFileName("sitemap-index.xml");

        $pagination = $this->blogRepository->getPagination(1, [], 100);
        $total_page = $pagination['total'];

        $output->writeln(sprintf('Total pages: %s', $total_page));

        for ($i = 1; $i <= $total_page; $i++) {
            $output->writeln(sprintf('-----------------'));
            $output->writeln(sprintf('Page: %s', $i));
            $posts = $this->blogRepository->getByPage($i, []);
            foreach($posts as $post) {
                $output->writeln(sprintf('Post: %s', $post['slug']));
                $date_update = \DateTime::createFromFormat('Y-m-d H:i:s', $post['updated_at']);
                if (!$date_update) {
                    $date_update = null;
                }
                $generator->addURL($post['slug'], $date_update, 'monthly', 0.2);
            }
        }

        $generator->createSitemap();
        $generator->writeSitemap();

        $output->writeln(sprintf('-----------------'));
        $output->writeln(sprintf('Generated sitemap.'));
        $output->writeln(sprintf('<info>Done</info>'));

        return 0;
    }
}
