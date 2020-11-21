<?php

declare(strict_types=1);

namespace WeBee\gCMS\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class PageFileConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('main');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('tags')
                    ->scalarPrototype()
                    ->end()
                    ->info('List of related tags')
                ->end()
                ->arrayNode('categories')
                    ->scalarPrototype()
                    ->end()
                    ->info('List of page categories')
                ->end()
                ->scalarNode('title')
                    ->info('Page title, used for html title attribute')
                ->end()
                ->scalarNode('slug')
                    ->info('Page address in url friendly format')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
