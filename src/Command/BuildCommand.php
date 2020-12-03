<?php

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use WeBee\gCMS\Command\AbstractCommand;
use WeBee\gCMS\FlexContent\Types\Blog;
use WeBee\gCMS\Helpers\FileSystem\DefaultFileSystem;
use WeBee\gCMS\Parsers\DefaultContentParser;
use WeBee\gCMS\Processors\DefaultConfigProcessor;
use WeBee\gCMS\Templates\DefaultTemplateManager;

class BuildCommand extends AbstractCommand
{
    /**
     * @inheritDoc
     */
    protected static $defaultName = 'build';

    /**
     * @inheritDoc
     */
    protected function addConfiguration()
    {
        $this->setDescription('Builds blog content (files + structure) for provided branch in defined repository');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
}
