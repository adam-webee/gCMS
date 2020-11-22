<?php

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use DomainException;
use IteratorAggregate;
use League\CommonMark\CommonMarkConverter as MdParser;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader as TwigLoader;
use WeBee\gCMS\Config\GenerateCommandConfig;
use WeBee\gCMS\Config\PageFileConfig;
use WeBee\gCMS\Content\PageFile;

class GenerateCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected static $defaultName = 'generate';

    /**
     * @var array<mixed> $config Command configuration
     */
    private $config = [];

    private $twig;

    /**
     * @var array<mixed> $dependencies Dependencies used by command
     */
    private $dependencies;

    public function __construct()
    {
        parent::__construct();

        $this->dependencies = [
            'configs' => [
                'processor' => new Processor(),
                'pageFileConfig' => new PageFileConfig(),
                'commandConfig' => new GenerateCommandConfig(),
            ],
            'mdParser' => new MdParser(),
            'fs' => new Filesystem(),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Generates blog content (files + structure) for provided branch in defined repository')
            ->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'Path to configuration file', null)
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input->getOption('config-file'));

        $this->twig = new TwigEnvironment(
            new TwigLoader($this->config['resources']['templates']),
            ['debug' => true]
        );
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());

        $fs = $this->dependencies['fs'];
        $outputPath = $this->config['output']['path'];
        $staticPath = $outputPath . $this->config['output']['static'];

        $fs->remove($outputPath);
        $fs->mkdir([$outputPath]);
        $fs->mirror($this->config['resources']['static'], $staticPath);

        $this->parseFiles();

        $output->writeln('Content generated!');

        return Command::SUCCESS;
    }

    /**
     * Loads configuration necessary for command execution.
     *
     * @param null|string $configFilePath Path to file with generation settings
     */
    private function loadConfig(?string $configFilePath)
    {
        if (null === $configFilePath || !file_exists($configFilePath)) {
            throw new DomainException('Configuration file not found');
        }

        $config = json_decode(
            file_get_contents($configFilePath),
            true
        );

        if (null === $config) {
            throw new DomainException('Configuration file is not a valid JSON file');
        }

        $this->config = $this
            ->dependencies['configs']['processor']
            ->processConfiguration(
                $this->dependencies['configs']['commandConfig'],
                [$this->config, $config]
            )
        ;
    }

    /**
     * Gets files for parsing.
     *
     * @return IteratorAggregate Files with content to parse
     */
    private function getFilesToParse(): IteratorAggregate
    {
        $finder = new Finder();

        return $finder
            ->ignoreVCS(true)
            ->ignoreVCSIgnored(true)
            ->ignoreUnreadableDirs(true)
            ->in($this->config['input']['path'])
            ->files()
            ->name('*.page.md')
            ->sortByModifiedTime()
            ->reverseSorting()
        ;
    }

    private function parseFiles()
    {
        $pages = [];
        $categories = [];

        $basePath = $this->config['output']['relative'] ? '' : $this->config['output']['path'];
        $staticPath = $basePath . $this->config['output']['static'];

        foreach ($this->getFilesToParse() as $file) {
            $page = new PageFile(
                $file,
                $this->dependencies['mdParser'],
                $this->dependencies['configs']['processor'],
                $this->dependencies['configs']['pageFileConfig']
            );

            $pages[$page->slug()] = $page;

            foreach ($page->categories() as $category) {
                if (!array_key_exists($category, $categories)) {
                    $categories[$category] = [
                        'name' => $category,
                        'slug' => sprintf('categories/%s', $category),
                        'pages' => [],
                    ];
                }

                $categories[$category]['pages'][] = $page;
            }
        }

        foreach ($pages as $pageSlug => $page) {
            $this->dependencies['fs']->dumpFile(
                implode('', [$this->config['output']['path'], $page->targetPath(), $page->targetFileName(), '.html']),
                $this->twig->render(
                    'page.twig',
                    [
                        'link' => [
                            'static' => $staticPath,
                            'base' => $basePath,
                        ],
                        'categories' => array_values($categories),
                        'page' => $page,
                    ]
                )
            );
        }

        foreach (array_values($categories) as $category) {
            dump($category['name'], count($category['pages']));
            $this->dependencies['fs']->dumpFile(
                implode('', [$this->config['output']['path'], '/', $category['slug'], '.html']),
                $this->twig->render(
                    'page_list.twig',
                    [
                        'link' => [
                            'static' => $staticPath,
                            'base' => $basePath,
                        ],
                        'categories' => array_values($categories),
                        'page' => [
                            'lang' => 'en',
                            'title' => $category['name'],
                            'tags' => [$category['name']],
                            'pages' => $category['pages'],
                        ],
                    ]
                )
            );
        }

        $this->dependencies['fs']->dumpFile(
            implode('', [$this->config['output']['path'], '/index.html']),
            $this->twig->render(
                'page_list.twig',
                [
                    'link' => [
                        'static' => $staticPath,
                        'base' => $basePath,
                    ],
                    'categories' => array_values($categories),
                    'page' => [
                        'lang' => 'en',
                        'title' => 'Latest articles',
                        'tags' => ['latest', 'articles', 'top10'],
                        'pages' => $pages,
                    ],
                ]
            )
        );
    }
}
