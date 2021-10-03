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
use WeBee\gCMS\FlexContent\ContentInterface;
use WeBee\gCMS\FlexContent\TypeFinder;
use WeBee\gCMS\FlexContent\Types\Blog;
use WeBee\gCMS\Helpers\FileSystem\DefaultFileSystem;
use WeBee\gCMS\Helpers\FileSystem\FileSystemInterface;
use WeBee\gCMS\Templates\TemplateManagerInterface;

class BuildCommand extends AbstractCommand
{
    private FileSystemInterface $fs;

    protected static $defaultName = 'build';

    protected function additionalConfiguration()
    {
        $this->setDescription('Builds blog content (files + structure) for provided branch in defined repository');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->fs = new DefaultFileSystem();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->makeOutputPath($this->config['output']['path']);

        $blog = $this->buildBlogInstance();
        $blog->load($this->buildBlogJsonConfig(), ['finder' => new Finder()]);

        $this->saveToFiles($blog);
        $this->publishMediaFiles();
        $this->publish();

        return Command::SUCCESS;
    }

    private function makeOutputPath(string $outputPath): void
    {
        if (!$this->fs->exists($outputPath)) {
            $this->fs->mkdir($outputPath);
        }
    }

    private function buildBlogInstance(): Blog
    {
        $templateManagerClass = $this->config['config']['templates']['manager'];
        $configProcessorClass = $this->config['config']['processor'];
        $parserManagerClass = $this->config['config']['parser']['manager'];

        $templateManager = new $templateManagerClass(
            $this->config['resources']['templates'],
            $this->config['config']['templates']
        );
        $configProcessor = new $configProcessorClass();
        $parserManager = new $parserManagerClass(
            ...$this->buildParsers(
                $this->config['config']['parser']['parsers'],
                $templateManager
            )
        );

        $this->registerContentTypes($this->config['config']['content']['types']);

        return new Blog($parserManager, $templateManager, $configProcessor, $this->fs);
    }

    private function buildParsers(
        array $parsersClassNames,
        TemplateManagerInterface $templateManager
    ): array {
        $parsersClassNames = array_unique($parsersClassNames);
        ksort($parsersClassNames);
        $parsers = [];

        foreach ($parsersClassNames as $parsingOrder => $parserClassName) {
            $parsers[] = new $parserClassName($templateManager);
        }

        return $parsers;
    }

    private function registerContentTypes(array $typesDefinitions): void
    {
        foreach ($typesDefinitions as $typeName => $typeClass) {
            TypeFinder::find()->registerType($typeClass, $typeName);
        }
    }

    private function buildBlogJsonConfig(): string
    {
        if ($this->config['output']['relative']) {
            $basePath = $this->config['url'] ? $this->config['url'] : '';
        } else {
            $basePath = realpath($this->config['output']['path']);
        }

        $staticPath = $basePath.$this->config['output']['static'];

        return json_encode([
            'name' => $this->config['name'],
            'slogan' => $this->config['slogan'],
            'path' => [
                'static' => $staticPath,
                'base' => $basePath,
                'extension' => $this->config['output']['extension'],
            ],
            'config' => [
                'sourcePath' => sprintf('%s/%s', $this->config['input']['path'], $this->config['input']['contentFolder']),
            ],
        ]);
    }

    /**
     * Recursively saves content to files.
     *
     * File names and structure will be extracted from slugs.
     *
     * @param ContentInterface $content  Content to be exported to file
     * @param array<string>    $exported [opt] Reference to list of slugs of already exported contents
     */
    private function saveToFiles(ContentInterface $content, array &$exported = []): void
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
            $this->saveToFiles($childContent, $exported);
        }
    }

    private function publishMediaFiles(): void
    {
        $mediaFinder = new Finder();
        $contentFolder = sprintf('%s/%s', $this->config['input']['path'], $this->config['input']['contentFolder']);
        $mediaFinder
            ->ignoreUnreadableDirs(true)
            ->ignoreVCS(true)
            ->in($contentFolder)
            ->files()
            ->name($this->config['config']['mediaFilesPattern']);

        $this->fs->mirror(
            $contentFolder,
            $this->config['output']['path'],
            $mediaFinder,
            // Until there is an error in Symfony Finder do not add option delete set to true
            // as it will remove files from source not only target directory!
            ['override' => true]
        );
    }

    private function publish(): void
    {
        $this->fs->mirror(
            $this->config['resources']['static'],
            sprintf('%s/%s', $this->config['output']['path'], $this->config['output']['static']),
            null,
            ['override' => true, 'delete' => true]
        );
    }
}
