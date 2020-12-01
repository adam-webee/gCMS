<?php

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use WeBee\gCMS\FlexContent\ContentInterface;

class PageConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('page');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode(ContentInterface::TAGS)
                    ->scalarPrototype()
                    ->end()
                    ->info('List of related tags.')
                ->end()
                ->arrayNode(ContentInterface::CATEGORIES)
                    ->scalarPrototype()
                    ->end()
                    ->info('List of page categories.')
                ->end()
                ->scalarNode(ContentInterface::TITLE)
                    ->info('Page title, used for html title attribute.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode(ContentInterface::SLUG)
                    ->info('Page address in url friendly format.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode(ContentInterface::EXCERPT)
                    ->info('Excerpt of a page to display it on listings. Can use markup.')
                    ->defaultValue('')
                ->end()
                ->scalarNode('lang')
                    ->info('Page language.')
                    ->defaultValue('en')
                ->end()
                ->scalarNode(ContentInterface::AUTHOR)
                    ->info('Author.')
                    ->defaultValue('')
                ->end()
                ->scalarNode('createDate')
                    ->info('Page date in Unix timestamp format.')
                    ->defaultValue(time())
                ->end()
            ->end();

        return $treeBuilder;
    }
}
