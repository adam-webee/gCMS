<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use WeBee\gCMS\FlexContent\ContentInterface;

class Listing extends Page
{
    protected function render()
    {
        $this->parseAttributes();

        $this->attributes['pages'] = array_map(
            function ($page) {
                return $page->get();
            },
            $this->getTopPages()
        );

        $this->attributes['menus'] = $this->getMenu();

        $this->renderedContent = $this->templateManager->render(
            'listing_page.twig',
            ['page' => $this->attributes]
        );
    }

    /**
     * Gets top X pages from parent content.
     *
     * @param int $numberOfPages defines max quantity of returned pages
     *
     * @return array<ContentInterface>
     */
    private function getTopPages(int $numberOfPages = 10): array
    {
        $pages = array_filter(
            $this->getParent()->getChildren(),
            function (ContentInterface $content) {
                $isPage = is_a($content, Page::class);
                $isNotThisPage = !is_a($content, get_class($this));
                $skip = $content->get(static::SKIP_FROM_LISTING);

                return $isPage && !$skip && $isNotThisPage;
            }
        );

        usort(
            $pages,
            function ($a, $b) {
                return $a->get('createDate', 1) <=> $b->get('createDate', 1);
            }
        );

        return array_slice($pages, 0, $numberOfPages, true);
    }

    protected function loadConfigDefinition()
    {
        $this->configDefinition = new PageConfig();
    }

    public function getRelationName(): string
    {
        return self::RELATION_TECH_CHILD;
    }
}
