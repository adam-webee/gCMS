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
use WeBee\gCMS\FlexContent\ContentInterface;

class MainPage extends Page
{
    protected function render()
    {
        $this->attributes = [
            self::SLUG => 'index',
            self::TITLE => 'Home',
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

        $this->attributes['pages'] = array_map(
            function ($page) {
                return $page->get();
            },
            $this->getTopPages()
        );

        $this->attributes['menus'] = $this->getMenu();

        $this->renderedContent = $this->templateManager->render(
            'main_page.twig',
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

                return $isPage && $isNotThisPage;
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
}
