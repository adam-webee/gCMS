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
use WeBee\gCMS\FlexContent\ContentRelationInterface;
use WeBee\gCMS\FlexContent\Types\Category;

class Blog extends AbstractContent
{
    private $config;

    /**
     * @inheritDoc
     */
    protected function render()
    {
        // Blog have no content. Blog only holds other content types in required structure (e.g. like tree).
        // But it can holds global configuration and other necessary data.
        $config = json_decode($this->rawContent, true);
        $this->config = is_null($config) ? [] : $config;

        $processedConfig = $this
            ->configProcessor
            ->process($this->configDefinition, [$this->config])
        ;

        $this->templateManager->addGlobals($processedConfig);

        $additional = ['extension' => $this->config['path']['extension']];
        $this->loadPart('{"menuItemNumber":0}', $additional, ContentRelationInterface::RELATION_TECH_CHILD, MainPage::class);
        $this->loadPart('{"menuItemNumber":1}', $additional, ContentRelationInterface::RELATION_TECH_CHILD, Category::class);

        $this->buildPagesFromSource();

        // We need to re-render technical children to include data from all regular content children
        foreach ($this->contentParts[ContentRelationInterface::RELATION_TECH_CHILD] as $content) {
            $content->render();
        }
    }

    /**
     * Builds blogs' children from source files.
     */
    private function buildPagesFromSource()
    {
        foreach ($this->getFilesToParse() as $file) {
            $file = $file->openFile('r');

            $this->loadPart(
                $file->fread($file->getSize()),
                ['file' => $file, 'extension' => $this->config['path']['extension']],
                ContentRelationInterface::RELATION_CHILD,
                Page::class
            );
        }
    }

    /**
     * Gets files for parsing.
     *
     * @return IteratorAggregate Files with content to parse
     */
    private function getFilesToParse(): IteratorAggregate
    {
        $finder = $this->additionalData['finder'];

        return $finder
            ->ignoreUnreadableDirs(true)
            ->ignoreVCS(true)
            ->in($this->config['config']['sourcePath'])
            ->files()
            ->name('*.page.md')
            ->sortByModifiedTime()
            ->reverseSorting()
        ;
    }

    /**
     * @inheritDoc
     */
    protected function loadConfigDefinition()
    {
        $this->configDefinition = new BlogConfig();
    }
}
