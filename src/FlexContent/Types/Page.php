<?php

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use DomainException;
use WeBee\gCMS\FlexContent\AbstractContent;
use WeBee\gCMS\FlexContent\ContentInterface;
use WeBee\gCMS\FlexContent\ContentRelationInterface;
use WeBee\gCMS\FlexContent\Types\PageConfig;

class Page extends AbstractContent
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
     * @var array<mixed> $attributes Page attributes [
     *  @var string ContentInterface::TITLE
     *  @var string ContentInterface::SLUG
     *  @var array<string> ContentInterface::TAGS
     *  @var array<string> ContentInterface::CATEGORIES
     * ]
     */
    protected $attributes = [
        ContentInterface::TAGS => [],
        ContentInterface::CATEGORIES => [],
    ];

    /**
     * @var PageConfig $configDefinition
     */
    protected $configDefinition;

    /**
     * @inheritDoc
     */
    protected function loadConfigDefinition()
    {
        $this->configDefinition = new PageConfig();
    }

    /**
     * @inheritDoc
     */
    protected function render()
    {
        $this->attributes = [
            ContentInterface::TAGS => [],
            ContentInterface::CATEGORIES => [],
        ];

        $this->parseAttributes();
        $this->parseAdditionalData();
        $this->attributes['content'] = $this->contentParser->parse(
            $this->findContent(self::PAGE_PATTERN_CONTENT)
        );
        $this->attributes['menus'] = $this->getMenu();
        $this->renderedContent =  $this->templateManager->render(
            'page.twig',
            ['page' => $this->attributes]
        );
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
        $contentExists = 1 === preg_match($contentExpression, $this->rawContent, $content);

        if ($contentExists) {
            return $content[1];
        }

        return null;
    }

    /**
     * Parse additional data into expected format.
     */
    private function parseAdditionalData()
    {
        if (array_key_exists('createDate', $this->additionalData)) {
            $this->attributes['createDate'] = $this->additionalData['createDate'];
        }
    }

    /**
     * Parse page attributes to expected array format.
     */
    private function parseAttributes()
    {
        $toParseAttributes = $this->findContent(self::PAGE_PATTERN_ATTRIBUTES);

        if (null === $toParseAttributes) {

            $this->attributes = $this
                ->configProcessor
                ->process($this->configDefinition, [
                    $this->attributes,
                    [
                        ContentInterface::TITLE => 'Undefined title',
                        ContentInterface::SLUG => sprintf('undefined/%s', bin2hex(random_bytes(15))),
                    ]
                ])
            ;

            $this->attributes[ContentInterface::SLUG] = $this->slug($this->attributes[ContentInterface::SLUG]);

            return;
        }

        $toParseAttributes = json_decode($toParseAttributes, true);

        if (null === $toParseAttributes) {
            throw new DomainException('Bad JSON - can not parse content attributes');
        }

        $this->attributes = $this
            ->configProcessor
            ->process(
                $this->configDefinition,
                // order of arrays matters, as we need to apply parsed attributes onto default one
                [$this->attributes, $toParseAttributes]
            )
        ;

        $this->attributes['excerpt'] = $this->contentParser->parse($this->attributes['excerpt']);
        $this->attributes[ContentInterface::SLUG] = $this->slug($this->attributes[ContentInterface::SLUG]);
    }

    /**
     * @inheritDoc
     */
    public function get(?string $whatToGet = null, $valueIfNotExists = null)
    {
        if (null === $whatToGet) {
            return $this->attributes;
        }

        if (array_key_exists($whatToGet, $this->attributes)) {
            return $this->attributes[$whatToGet];
        }

        return $valueIfNotExists;
    }

    /**
     * @return array<array>
     */
    protected function getMenu(): array
    {
        $menuPages = [];

        foreach ($this->getParent()->getAll() as $page) {
            if (-1 == $page->get('menuItemNumber', -1)) {
                continue;
            }

            $menuPages[$page->slug()] = $page->get();
        }

        usort($menuPages, function ($a, $b) { return $a['menuItemNumber'] <=> $b['menuItemNumber']; });

        return $menuPages;
    }
}
