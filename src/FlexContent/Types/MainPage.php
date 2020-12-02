<?php

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use DomainException;
use WeBee\gCMS\FlexContent\ContentInterface;
use WeBee\gCMS\FlexContent\Types\Page;
use WeBee\gCMS\FlexContent\Types\PageConfig;

class MainPage extends Page
{
    /**
     * @inheritDoc
     */
    protected function render()
    {
        $this->attributes = [
            ContentInterface::SLUG => 'index',
            ContentInterface::TITLE => 'Home',
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

        $this->attributes[ContentInterface::SLUG] = $this->slug($this->attributes[ContentInterface::SLUG]);

        $this->attributes['pages'] = array_map(
            function ($page) { return $page->get(); },
            $this->getTopPages()
        );

        $this->attributes['menus'] = $this->getMenu();

        $this->renderedContent =  $this->templateManager->render(
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
            function ($content) {
                return (is_a($content, Page::class) && !is_a($content, get_class($this)));
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
