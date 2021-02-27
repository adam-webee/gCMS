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

class Category extends Page
{
    /**
     * @var array<ContentInterface>
     */
    private array $categories = [];

    protected function render()
    {
        $this->attributes = [
            self::SLUG => 'categories',
            self::TITLE => 'Categories',
        ];

        $toParseAttributes = json_decode($this->rawContent, true);

        if (null === $toParseAttributes) {
            throw new DomainException('Bad JSON - can not parse main page attributes');
        }

        $this->attributes = $this
            ->configProcessor
            ->process(
                $this->configDefinition,
                // order of arrays matters, as we need to apply parsed attributes onto default one
                [$this->attributes, $toParseAttributes]
            )
        ;

        $this->attributes[self::SLUG] = $this->slug($this->attributes[self::SLUG]);
        $this->buildCategories();
        $this->attributes['categoryMap'] = $this->categories;
        $this->attributes['menus'] = $this->getMenu();

        $this->renderedContent = $this->templateManager->render(
            'category.twig',
            ['page' => $this->attributes]
        );
    }

    /**
     * Builds info about all categories from parent contents.
     */
    private function buildCategories()
    {
        $visitedPages = [];
        $pagesInCategory = [];

        $this->categories = [];

        foreach ($this->getParent()->getChildren() as $page) {
            $categories = $page->get('categories', []);
            $slug = $page->slug();

            if (empty($categories) || array_key_exists($slug, $visitedPages)) {
                continue;
            }

            foreach ($categories as $category) {
                if (!array_key_exists($category, $this->categories)) {
                    $pagesInCategory[$category] = [];
                    $this->categories[$category] = [
                        'name' => $category,
                        'pages' => [],
                    ];
                }

                if (!array_key_exists($slug, $pagesInCategory[$category])) {
                    $this->categories[$category]['pages'][] = $page->get();
                    $pagesInCategory[$category][$slug] = true;
                }
            }

            $visitedPages[$slug] = true;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function loadConfigDefinition()
    {
        $this->configDefinition = new PageConfig();
    }
}
