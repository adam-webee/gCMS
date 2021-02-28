<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use IteratorAggregate;
use WeBee\gCMS\FlexContent\AbstractContent;
use WeBee\gCMS\FlexContent\TypeFinder;

class Blog extends AbstractContent
{
    private array $config;

    protected function render()
    {
        $processedConfig = $this->processBlogConfig();

        $this->templateManager->addGlobals($processedConfig);
        $this->buildChildrenPages();

        // We need to re-render all to make sure, that dynamic content (menus, etc.) are all up to date on all children
        foreach ($this->getAll() as $content) {
            $content->render();
        }
    }

    private function processBlogConfig(): array
    {
        $config = json_decode($this->rawContent, true);
        $this->config = $config ?? [];

        return $this
            ->configProcessor
            ->process($this->configDefinition, [$this->config])
        ;
    }

    private function buildChildrenPages()
    {
        foreach ($this->getFilesToParse() as $file) {
            $file = $file->openFile('r');

            $this->loadPart(
                $file->fread($file->getSize()),
                ['file' => $file, 'extension' => $this->config['path']['extension']],
                TypeFinder::find()->byFile($file)
            );
        }
    }

    private function getFilesToParse(): IteratorAggregate
    {
        $finder = $this->additionalData['finder'];

        return $finder
            ->ignoreUnreadableDirs(true)
            ->ignoreVCS(true)
            ->in($this->config['config']['sourcePath'])
            ->files()
            ->name(TypeFinder::FILE_NAME_PATTERN)
            ->sortByModifiedTime()
            ->reverseSorting()
        ;
    }

    protected function loadConfigDefinition()
    {
        $this->configDefinition = new BlogConfig();
    }

    public function getRelationName(): string
    {
        return self::RELATION_PARENT;
    }
}
