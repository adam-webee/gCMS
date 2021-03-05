<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use DomainException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use WeBee\gCMS\FlexContent\AbstractContent;

class Page extends AbstractContent
{
    protected const PAGE_PATTERN_ATTRIBUTES = '/^[\s]*[`]{3}json([\s\S]*?)[`]{3}$/ims';

    protected const PAGE_PATTERN_CONTENT = '/^(?:[\s]*[`]{3}json[\s\S]*?[`]{3})\n([\s\S]*)$/ims';

    protected array $attributes = [
        self::TAGS => [],
        self::CATEGORIES => [],
    ];

    protected ConfigurationInterface $configDefinition;

    protected function loadConfigDefinition()
    {
        $this->configDefinition = new PageConfig();
    }

    protected function render()
    {
        $this->attributes = [
            self::TAGS => [],
            self::CATEGORIES => [],
        ];

        $this->parseAttributes();
        $this->parseAdditionalData();
        $this->attributes['content'] = $this->parserManager->parse(
            $this->extractContent(self::PAGE_PATTERN_CONTENT)
        );

        $this->attributes['menus'] = $this->getMenu();
        $this->renderedContent = $this->templateManager->render(
            'page.twig',
            ['page' => $this->attributes]
        );
    }

    private function extractContent(string $contentExpression): ?string
    {
        $content = [];
        $contentExists = 1 === preg_match($contentExpression, $this->rawContent, $content);

        if ($contentExists) {
            return $content[1];
        }

        return null;
    }

    private function parseAdditionalData()
    {
        if (array_key_exists('createDate', $this->additionalData)) {
            $this->attributes['createDate'] = $this->additionalData['createDate'];
        }
    }

    protected function parseAttributes()
    {
        $toParseAttributes = $this->extractContent(self::PAGE_PATTERN_ATTRIBUTES);

        if (null === $toParseAttributes) {
            $this->attributes = $this
                ->configProcessor
                ->process(
                    $this->configDefinition,
                    [$this->attributes, $this->buildDefaultMainAttributes()]
                )
            ;

            $this->attributes[self::SLUG] = $this->slug($this->attributes[self::SLUG]);

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

        $this->attributes['excerpt'] = $this->parserManager->parse($this->attributes['excerpt']);
        $this->attributes[self::SLUG] = $this->slug($this->attributes[self::SLUG]);
    }

    private function buildDefaultMainAttributes(): array
    {
        return [
            self::TITLE => 'Undefined title',
            self::SLUG => sprintf('undefined/%s', bin2hex(random_bytes(15))),
        ];
    }

    protected function getMenu(): array
    {
        $menuPages = [];

        foreach ($this->getParent()->getAll() as $page) {
            if (-1 == $page->get('menuItemNumber', -1)) {
                continue;
            }

            $menuPages[$page->slug()] = $page->get();
        }

        usort($menuPages, function ($a, $b) {
            return $a['menuItemNumber'] <=> $b['menuItemNumber'];
        });

        return $menuPages;
    }

    public function getRelationName(): string
    {
        return self::RELATION_CHILD;
    }
}
