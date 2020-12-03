<?php

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use WeBee\gCMS\Command\CommandConfig;
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

        $templateManager = new DefaultTemplateManager($this->config['resources']['templates'], ['debug' => true]);
        $configProcessor = new DefaultConfigProcessor();
        $contentParser = new DefaultContentParser();
        $fs = new DefaultFileSystem();

        if (!$fs->exists($this->config['output']['path'])) {
            $fs->mkdir($this->config['output']['path']);
        }

        $basePath = $this->config['output']['relative'] ? '' : realpath($this->config['output']['path']);
        $staticPath = $basePath . $this->config['output']['static'];
        $output->writeln($this->config['output']['extension']);
        $blog = new Blog($contentParser, $templateManager, $configProcessor, $fs);
        $blog->load(
            json_encode([
                'name' => $this->config['name'],
                'slogan' => $this->config['slogan'],
                'path' => [
                    'static' => $staticPath,
                    'base' => $basePath,
                    'extension' => $this->config['output']['extension'],
                ],
                'config' => [
                    'sourcePath' => $this->config['input']['path'],
                ]
            ]),
            ['finder' => new Finder()]
        );
        $blog->export($this->config['output']['path']);
        $fs->mirror(
            $this->config['resources']['static'],
            sprintf('%s/%s', $this->config['output']['path'], $this->config['output']['static']),
            null,
            ['override' => true, 'delete' => true]
        );

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
                new CommandConfig(),
                [$this->config, $config]
            )
        ;
    }
}
