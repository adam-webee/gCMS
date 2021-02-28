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
    protected ContentParserInterface $contentParser;

    protected TemplateManagerInterface $templateManager;

    protected ConfigProcessorInterface $configProcessor;

    protected string $rawContent = '';

    protected string $renderedContent = '';

    protected array $additionalData = [];

    protected array $contentParts = [
        self::RELATION_CHILD => [],
        self::RELATION_RELATED => [],
        self::RELATION_TECH_CHILD => [],
        self::RELATION_PARENT => [null],
    ];

    protected string $slug = '';

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

    public function load(string $rawContent, array $additionalData = []): ContentInterface
    {
        $this->rawContent = $rawContent;
        $this->additionalData = array_merge($this->additionalData, $additionalData);
        $this->render();

        return $this;
    }

    public function loadPart(
        string $rawContent,
        array $additionalData = [],
        ?string $typeName = null,
        ?string $relation = null
    ): ContentInterface {
        if (empty($typeName)) {
            $typeName = self::class;
        }

        $content = $this->buildNewContentInstance($typeName);

        $this->appendContentPart($content, $relation ?? $content->getRelationName());

        return $content->load($rawContent, $additionalData);
    }

    private function isParentNeeded(string $relation): bool
    {
        return in_array($relation, [self::RELATION_CHILD, self::RELATION_TECH_CHILD]);
    }

    private function appendContentPart(ContentInterface &$content, string $relation): void
    {
        $this->contentParts[$relation][] = $content;

        if ($this->isParentNeeded($relation)) {
            $content->setParent($this);
        }
    }

    private function buildNewContentInstance(string $fullyQualifiedClassName): ContentInterface
    {
        if (!class_exists($fullyQualifiedClassName)) {
            throw new DomainException(sprintf('Requested content type class "%s" does not exists', $fullyQualifiedClassName));
        }

        return new $fullyQualifiedClassName(
            $this->contentParser,
            $this->templateManager,
            $this->configProcessor
        );
    }

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

    public function setParent(ContentInterface $parentContent): ContentRelationInterface
    {
        $this->contentParts[self::RELATION_PARENT][0] = $parentContent;

        return $this;
    }

    public function getParent(): ?ContentInterface
    {
        return $this->contentParts[self::RELATION_PARENT][0];
    }

    public function appendChild(ContentInterface $childContent): ContentRelationInterface
    {
        $this->contentParts[self::RELATION_CHILD][] = $childContent;

        return $this;
    }

    public function getChildren(): array
    {
        return $this->contentParts[self::RELATION_CHILD];
    }

    public function appendRelated(ContentInterface $relatedContent): ContentRelationInterface
    {
        $this->contentParts[self::RELATION_RELATED][] = $relatedContent;

        return $this;
    }

    public function getRelated(): array
    {
        return $this->contentParts[self::RELATION_RELATED];
    }

    public function getAll(): array
    {
        $contents = [];

        $parent = $this->getParent();

        if (!is_null($parent)) {
            $contents[] = $parent;
        }

        foreach ($this->contentParts as $relationType => $relatedContents) {
            if (self::RELATION_PARENT === $relationType) {
                continue;
            }

            $contents = array_merge($contents, $relatedContents);
        }

        return $contents;
    }

    public function get(?string $whatToGet = null, $valueIfNotExists = null): mixed
    {
        if (null === $whatToGet) {
            return $this->attributes;
        }

        if (array_key_exists($whatToGet, $this->attributes)) {
            return $this->attributes[$whatToGet];
        }

        return $valueIfNotExists;
    }
}
