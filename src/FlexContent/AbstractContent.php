<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent;

use DomainException;
use WeBee\gCMS\FlexContent\ContentInterface as ContentInterface;
use WeBee\gCMS\FlexContent\ContentRelationInterface as ContentRelationInterface;
use WeBee\gCMS\Parsers\ContentParserInterface;
use WeBee\gCMS\Processors\ConfigProcessorInterface;
use WeBee\gCMS\Templates\TemplateManagerInterface;

abstract class AbstractContent implements ContentInterface, ContentRelationInterface
{
    /**
     * @var ParserInterface $contentParser
     */
    protected $contentParser;

    /**
     * @var TemplateManagerInterface $templateManager
     */
    protected $templateManager;

    /**
     * @var ConfigProcessorInterface $configProcessor
     */
    protected $configProcessor;

    /**
     * @var string $rawContent
     */
    protected $rawContent = '';

    /**
     * @var string $renderedContent
     */
    protected $renderedContent = '';

    /**
     * @var array<mixed> $additionalData
     */
    protected $additionalData = [];

    /**
     * @var array $contentParts [
     *  @var array<ContentInterface> [RELATION_TYPE]
     * ]
     */
    protected $contentParts = [
        ContentRelationInterface::RELATION_CHILD => [],
        ContentRelationInterface::RELATION_RELATED => [],
        ContentRelationInterface::RELATION_TECH_CHILD => [],
        ContentRelationInterface::RELATION_PARENT => [null],
    ];

    /**
     * @var string $slug
     */
    protected $slug = '';

    /**
     * @inheritDoc
     */
    public function __construct(
        ContentParserInterface $contentParser,
        TemplateManagerInterface $templatesManager,
        ConfigProcessorInterface $configurationProcessor
    ) {
        $this->contentParser = $contentParser;
        $this->templateManager = $templatesManager;
        $this->configProcessor = $configurationProcessor;
        $this->loadConfigDefinition();
    }

    /**
     * @inheritDoc
     */
    public function slug(?string $slug = null): string
    {
        if (is_null($slug)) {
            return $this->slug;
        }

        $this->slug = $slug;

        $extension = array_key_exists('extension', $this->additionalData) ? $this->additionalData['extension'] : '';

        if (!preg_match("/{$extension}$/", $this->slug)) {
            $this->slug .= $extension;
        }

        return $this->slug;
    }

    /**
     * @inheritDoc
     */
    public function load(string $rawContent, array $additionalData = []): ContentInterface
    {
        $this->rawContent = $rawContent;
        $this->additionalData = array_merge($this->additionalData, $additionalData);
        $this->render();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function loadPart(
        string $rawContent,
        array $additionalData = [],
        string $relation = ContentRelationInterface::RELATION_CHILD,
        ?string $typeName = null
    ): ContentInterface {
        if (empty($typeName)) {
            $typeName = self::class;
        }

        $content = $this->buildNewContentInstance($typeName);

        $this->contentParts[$relation][] = $content;

        if (
            in_array(
                $relation,
                [ContentRelationInterface::RELATION_CHILD, ContentRelationInterface::RELATION_TECH_CHILD]
            )
        ) {
            $content->setParent($this);
        }

        return $content->load($rawContent, $additionalData);
    }

    /**
     * Creates new instance of content - that will be part of current content.
     *
     * @param string $className Fully qualified class name
     *
     * @return ContentInterface New content instance
     */
    private function buildNewContentInstance(string $className): ContentInterface
    {
        if (!class_exists($className)) {
            throw new DomainException(sprintf('Requested content type class "%s" does not exists', $className));
        }

        return new $className(
            $this->contentParser,
            $this->templateManager,
            $this->configProcessor
        );
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->renderedContent;
    }

    /**
     * Loads configuration definition.
     *
     * Configuration definition will be a composite for particular content implementations.
     * This is why below must be left empty and if in concrete class configuration is necessary
     * then it must be overloaded. This way concrete classes will not need constructor modifications.
     */
    protected function loadConfigDefinition()
    {
    }

    /**
     * Renders content to its expected format.
     */
    protected function render()
    {
    }

    /**
     * @inheritDoc
     */
    public function setParent(ContentInterface $parentContent): ContentRelationInterface
    {
        $this->contentParts[ContentRelationInterface::RELATION_PARENT][0] = $parentContent;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?ContentInterface
    {
        return $this->contentParts[ContentRelationInterface::RELATION_PARENT][0];
    }

    /**
     * @inheritDoc
     */
    public function appendChild(ContentInterface $childContent): ContentRelationInterface
    {
        $this->contentParts[ContentRelationInterface::RELATION_CHILD][] = $childContent;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return $this->contentParts[ContentRelationInterface::RELATION_CHILD];
    }

    /**
     * @inheritDoc
     */
    public function appendRelated(ContentInterface $relatedContent): ContentRelationInterface
    {
        $this->contentParts[ContentRelationInterface::RELATION_RELATED][] = $relatedContent;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRelated(): array
    {
        return $this->contentParts[ContentRelationInterface::RELATION_RELATED];
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $contents = [];

        $parent = $this->getParent();

        if (!is_null($parent)) {
            $contents[] = $parent;
        }

        foreach ($this->contentParts as $relationType => $relatedContents) {
            if (ContentRelationInterface::RELATION_PARENT === $relationType) {
                continue;
            }

            $contents = array_merge($contents, $relatedContents);
        }

        return $contents;


        return array_merge(
            $contents,
            $this->getChildren(),
            $this->getRelated(),
        );
    }

    /**
     * @inheritDoc
     */
    public function get(?string $whatToGet = null, $valueIfNotExists = null)
    {
        return $valueIfNotExists;
    }
}
