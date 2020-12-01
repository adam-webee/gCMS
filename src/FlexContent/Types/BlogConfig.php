<?php

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class BlogConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('blog');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('path')
                    ->children()
                        ->scalarNode('base')
                            ->info('Base for links urls. E.g. "/" or "C:\path" or "/path/to/"')
                            ->defaultValue('/')
                        ->end()
                        ->scalarNode('static')
                            ->info('Relative to "base", path for static content like images, styles or js scripts')
                            ->defaultValue('static/')
                        ->end()
                        ->scalarNode('categories')
                            ->info('Relative to "base", path for category pages')
                            ->defaultValue('categories/')
                        ->end()
                        ->scalarNode('tags')
                            ->info('Relative to "base", path for tags pages')
                            ->defaultValue('tags/')
                        ->end()
                        ->scalarNode('extension')
                            ->info('Extension for generated files and slugs')
                            ->defaultValue('.ala')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('tags')
                    ->scalarPrototype()
                    ->end()
                    ->info('List of related tags.')
                ->end()
                ->scalarNode('name')
                    ->info('Blog name')
                    ->defaultValue('')
                ->end()
                ->scalarNode('slogan')
                    ->info('Blog slogan')
                    ->defaultValue('')
                ->end()
                ->arrayNode('config')
                    ->children()
                        ->scalarNode('sourcePath')
                            ->info('Path from where read blog content from')
                            ->defaultValue(__DIR__)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
