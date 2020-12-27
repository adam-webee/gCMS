<?php

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use DomainException;
use WeBee\gCMS\FlexContent\ContentInterface;
use WeBee\gCMS\FlexContent\Types\Page;
use WeBee\gCMS\FlexContent\Types\PageConfig;

class Category extends Page
{
    /**
     * @var array<ContentInterface> $categories
     */
    private $categories = [];

    /**
     * @inheritDoc
     */
    protected function render()
    {
        $this->attributes = [
            ContentInterface::SLUG => 'categories',
            ContentInterface::TITLE => 'Categories',
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

        $this->attributes['slug'] = $this->slug($this->attributes['slug']);
        $this->buildCategories();
        $this->attributes['categoryMap'] = $this->categories;
        $this->attributes['menus'] = $this->getMenu();

        $this->renderedContent =  $this->templateManager->render(
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
     * @inheritDoc
     */
    public function export(string $targetPath = 'output', array $exported = []): array
    {
        $this->render();
        $exported[] = '';

        $this->fs->dumpFile(sprintf('%s//%s', $targetPath, $this->slug()), $this->renderedContent);

        return $exported;
    }

    /**
     * @inheritDoc
     */
    protected function loadConfigDefinition()
    {
        $this->configDefinition = new PageConfig();
    }
}
