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
        $this->attributes[ContentInterface::SLUG] = 'categories';
        $this->attributes[ContentInterface::TITLE] = 'Categories';

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

        $this->slug = $this->attributes['slug'];
        $this->buildCategories();
        $this->attributes['categoryMap'] = $this->categories;

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
        $pages = array_filter(
            $this->getParent()->getChildren(),
            function ($content) {
                return (is_a($content, Page::class) && !is_a($content, get_class($this)));
            }
        );

        foreach ($pages as $page) {
            foreach ($page->get('categories') as $category) {
                if (!array_key_exists($category, $this->categories)) {
                    $this->categories[$category] = [
                        'name' => $category,
                        'slug' => sprintf('%s/%s', $this->attributes[ContentInterface::SLUG], $category),
                        'pages' => [],
                    ];
                }

                $this->categories[$category]['pages'][] = $page->get();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function export(string $targetPath = 'output', array $exported = []): array
    {
        $exported[] = '';

        $this->fs->dumpFile(sprintf('%s//%s.html', $targetPath, $this->slug), $this->renderedContent);

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
