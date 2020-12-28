<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use WeBee\gCMS\Command\AbstractCommand;
use WeBee\gCMS\FlexContent\ContentInterface;
use WeBee\gCMS\FlexContent\Types\Blog;
use WeBee\gCMS\Helpers\FileSystem\DefaultFileSystem;
use WeBee\gCMS\Parsers\DefaultContentParser;
use WeBee\gCMS\Processors\DefaultConfigProcessor;
use WeBee\gCMS\Templates\DefaultTemplateManager;

class BuildCommand extends AbstractCommand
{
    /**
     * @var DefaultFileSystem $fs
     */
    private $fs;

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
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->fs = new DefaultFileSystem();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateManager = new DefaultTemplateManager($this->config['resources']['templates'], ['debug' => true]);
        $configProcessor = new DefaultConfigProcessor();
        $contentParser = new DefaultContentParser();

        if (!$this->fs->exists($this->config['output']['path'])) {
            $this->fs->mkdir($this->config['output']['path']);
        }

        $basePath = $this->config['output']['relative'] ? '' : realpath($this->config['output']['path']);
        $staticPath = $basePath . $this->config['output']['static'];

        $blog = new Blog($contentParser, $templateManager, $configProcessor, $this->fs);
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

        $this->exportToFile($blog);
        $this->fs->mirror(
            $this->config['resources']['static'],
            sprintf('%s/%s', $this->config['output']['path'], $this->config['output']['static']),
            null,
            ['override' => true, 'delete' => true]
        );

        return Command::SUCCESS;
    }

    /**
     * Export content to defined output path.
     *
     * File names and structure will be made of slug.
     *
     * @param ContentInterface $content Content to be exported to file
     * @param array<string> $exported [opt] Reference to list of slugs of already exported contents
     */
    private function exportToFile(ContentInterface $content, array &$exported = []): void
    {
        $slug = $content->slug();

        if (in_array($slug, $exported)) {
            return;
        }

        if (!empty($slug)) {
            $this->fs->dumpFile(sprintf('%s//%s', $this->config['output']['path'], $slug), $content);
        }

        $exported[] = $slug;

        foreach ($content->getAll() as $childContent) {
            $this->exportToFile($childContent, $exported);
        }
    }
}
