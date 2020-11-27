<?php

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use IteratorAggregate;
use WeBee\gCMS\FlexContent\AbstractContent;
use WeBee\gCMS\FlexContent\ContentRelationInterface;

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

        $this->buildPagesFromSource();
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
                ['file' => $file],
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
            ->ignoreVCS(true)
            ->ignoreVCSIgnored(true)
            ->ignoreUnreadableDirs(true)
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
    public function export(string $targetPath = 'output', array $exported = []): array
    {
        foreach ($this->getAll() as $content) {
            $exported = array_merge(
                $exported,
                $content->export($targetPath, $exported)
            );
        }

        $this->loadPart('{}', [], ContentRelationInterface::RELATION_CHILD, MainPage::class)->export($targetPath, $exported);
        return $exported;
    }

    /**
     * @inheritDoc
     */
    protected function loadConfigDefinition()
    {
        $this->configDefinition = new BlogConfig();
    }
}
