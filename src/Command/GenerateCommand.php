<?php

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use WeBee\gCMS\Config\GenerateCommandConfig;
use WeBee\gCMS\FlexContent\Types\Blog;
use WeBee\gCMS\Helpers\FileSystem\DefaultFileSystem;
use WeBee\gCMS\Parsers\DefaultContentParser;
use WeBee\gCMS\Processors\DefaultConfigProcessor;
use WeBee\gCMS\Templates\DefaultTemplateManager;

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

        $basePath = $this->config['output']['relative'] ? '' : $this->config['output']['path'];
        $staticPath = $basePath . $this->config['output']['static'];

        $templateManager = new DefaultTemplateManager($this->config['resources']['templates'], ['debug' => true]);
        $configProcessor = new DefaultConfigProcessor();
        $contentParser = new DefaultContentParser();
        $fs = new DefaultFileSystem();

        $blog = new Blog($contentParser, $templateManager, $configProcessor, $fs);
        $blog->load(
            json_encode([
                'name' => 'WeBee.School',
                'slogan' => 'This is best slogan',
                'path' => [
                    'static' => $staticPath,
                    'base' => $basePath,
                ],
                'config' => [
                    'sourcePath' => $this->config['input']['path'],
                ]
            ]),
            ['finder' => new Finder()]
        );
        $blog->export($this->config['output']['path']);

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

        $config = json_decode(file_get_contents($configFilePath), true);

        if (null === $config) {
            throw new DomainException('Configuration file is not a valid JSON file');
        }

        $this->config = (new DefaultConfigProcessor())
            ->process(
                new GenerateCommandConfig(),
                [$this->config, $config]
            )
        ;
    }
}
