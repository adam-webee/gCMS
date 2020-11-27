<?php

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use DateTimeImmutable;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class PageConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('page');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('tags')
                    ->scalarPrototype()
                    ->end()
                    ->info('List of related tags.')
                ->end()
                ->arrayNode('categories')
                    ->scalarPrototype()
                    ->end()
                    ->info('List of page categories.')
                ->end()
                ->scalarNode('title')
                    ->info('Page title, used for html title attribute.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('slug')
                    ->info('Page address in url friendly format.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('excerpt')
                    ->info('Excerpt of a page to display it on listings. Can use markup.')
                    ->defaultValue('')
                ->end()
                ->scalarNode('lang')
                    ->info('Page language.')
                    ->defaultValue('en')
                ->end()
                ->scalarNode('author')
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
