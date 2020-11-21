<?php

declare(strict_types=1);

namespace WeBee\gCMS\Content;

use League\CommonMark\CommonMarkConverter as MdParser;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Finder\SplFileInfo;

class PageFile extends AbstractContent
{
    /**
     * @var string PAGE_PATTERN_ATTRIBUTES Regular expression pattern to extract attributes from raw page
     */
    const PAGE_PATTERN_ATTRIBUTES = '/^[\s]*[`]{3}json([\s\S]*?)[`]{3}$/ims';

    /**
     * @var string PAGE_PATTERN_CONTENT Regular expression pattern to extract content from raw page
     */
    const PAGE_PATTERN_CONTENT = '/^(?:[\s]*[`]{3}json[\s\S]*?[`]{3})\n([\s\S]*)$/ims';

    /**
     * @var Parsedown $mdParser Parser used to parse markup into valid html
     */
    private $mdParser;

    /**
     * @var SplFileInfo $fileInfo
     */
    private $fileInfo;

    /**
     * @var SplFileObject $file
     */
    private $file;

    /**
     * @var string $fileContent
     */
    private $fileContent;

    /**
     * @var Processor $configProcessor
     */
    private $configProcessor;

    /**
     * @var ConfigurationInterface $pageConfigDefinition
     */
    private $pageConfigDefinition;

    /**
     * @param SplFileInfo $fileInfo Info about file with page content
     * @param Parsedown $mdParser Page content markup parser
     * @param Processor $configProcessor Page configuration processor
     * @param ConfigurationInterface $pageConfigDefinition Page configuration definition to be used by processor
     */
    public function __construct(
        SplFileInfo $fileInfo,
        MdParser $mdParser,
        Processor $configProcessor,
        ConfigurationInterface $pageConfigDefinition
    ) {
        $this->fileInfo = $fileInfo;
        $this->mdParser = $mdParser;
        $this->configProcessor = $configProcessor;
        $this->pageConfigDefinition =  $pageConfigDefinition;

        $this->parse();
    }

    /**
     * @inheritDoc
     */
    protected function parse()
    {
        $this->file = $this->fileInfo->openFile('r');
        $this->fileContent = $this->file->fread($this->file->getSize());

        $this->parseAttributes();
        $this->parseContent();
    }

    /**
     * Looks for defined content in raw page content.
     *
     * @param string $contentDefinition Regular expression of content to find
     *
     * @return null|string First match from provided regular expression
     */
    private function findContent(string $contentExpression): ?string
    {
        $content = [];
        $contentExists = 1 === preg_match($contentExpression, $this->fileContent, $content);

        if ($contentExists) {
            return $content[1];
        }

        return null;
    }

    /**
     * Parse page attributes to expected array format.
     */
    private function parseAttributes()
    {
        $toParseAttributes = $this->findContent(self::PAGE_PATTERN_ATTRIBUTES);

        if (null === $toParseAttributes) {
            return;
        }

        $toParseAttributes = json_decode($toParseAttributes, true);

        if (null === $toParseAttributes) {
            return;
        }

        $this->attributes = $this->configProcessor->processConfiguration(
            $this->pageConfigDefinition,
            // order of arrays matters, as we need to apply parsed attributes onto default one
            [$this->attributes, $toParseAttributes]
        );
    }

    /**
     * Parse page content to expected string format.
     */
    private function parseContent()
    {
        $toParseContent = $this->findContent(self::PAGE_PATTERN_CONTENT);

        if (null === $toParseContent) {
            return;
        }

        $this->content = $this->mdParser->convertToHtml($toParseContent);
    }

    /**
     * @return string Page target file name
     */
    public function targetFileName(): string
    {
        $slugParts = explode('/', $this->slug());

        return $slugParts[array_key_last($slugParts)];
    }

    /**
     * @return string Page target path
     */
    public function targetPath(): string
    {
        $slugParts = explode('/', $this->slug());

        if (1 >= count($slugParts)) {
            return '/';
        }

        array_pop($slugParts);

        return sprintf('/%s/', implode('/', $slugParts));
    }
}
