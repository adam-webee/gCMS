<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CommandConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('main');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('name')
                    ->info('Generated content name e.g. blogs name or page name')
                    ->defaultValue('')
                ->end()
                ->scalarNode('slogan')
                    ->info('Generated content slogan')
                    ->defaultValue('')
                ->end()
                ->scalarNode('url')
                    ->info('Blog\'s URL. E.g. https://webee.school')
                    ->defaultValue('')
                ->end()
                ->append($this->defineInputSection())
                ->append($this->defineOutputSection())
                ->append($this->defineResourcesSection())
                ->append($this->defineConfigSection())
            ->end()
            ->validate()
                ->ifTrue(
                    function ($v) {
                        $isGitType = 'git' === $v['input']['type'];
                        $isBranchDefined = !empty($v['input']['branch']);

                        return $isGitType && !$isBranchDefined;
                    }
                )
                ->thenInvalid('Branch name must be provided if input type is "git"')
            ->end()
        ;

        return $treeBuilder;
    }

    private function defineInputSection()
    {
        $treeBuilder = new TreeBuilder('input');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->enumNode('type')
                    ->info('Type of input')
                    ->values(['git', 'folder'])
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('git')
                ->end()
                ->scalarNode('vcsSource')
                    ->info('Remote repository path')
                    ->defaultValue('')
                ->end()
                ->booleanNode('forceCheckout')
                    ->info('Force checkout on dirty repository')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('branch')
                    ->info('Branch name to switch to before generation')
                    ->defaultValue('master')
                ->end()
                ->scalarNode('path')
                    ->info('Path to source')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('contentFolder')
                    ->info('Name of a folder with content to parse - relative to path')
                    ->defaultValue('/content')
                ->end()
            ->end()
        ;

        return $node;
    }

    private function defineOutputSection()
    {
        $treeBuilder = new TreeBuilder('output');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('path')
                    ->info('Destination folder path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('static')
                    ->info('Static files folder path - relative to path')
                    ->defaultValue('/static')
                ->end()
                ->booleanNode('relative')
                    ->isRequired()
                    ->defaultValue(true)
                ->end()
                ->scalarNode('extension')
                    ->info('Extension for generated files')
                    ->defaultValue('.html')
                ->end()
            ->end()
        ;

        return $node;
    }

    private function defineResourcesSection()
    {
        $treeBuilder = new TreeBuilder('resources');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('templates')
                    ->info('Templates folder path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('static')
                    ->info('Path to folder with source static files')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function defineConfigSection()
    {
        $treeBuilder = new TreeBuilder('config');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('mediaFilesPattern')
                    ->info('Define media files names pattern')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->scalarNode('processor')
                    ->info('Qualified name of a class that will serve as configuration processor. Must fulfill WeBee\gCMS\Processors\ConfigProcessorInterface')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->append($this->defineTemplateConfiguration())
                ->append($this->defineParsersConfiguration())
                ->append($this->defineContentTypes())
            ->end()
        ;

        return $node;
    }

    private function defineTemplateConfiguration()
    {
        $treeBuilder = new TreeBuilder('templates');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('manager')
                    ->info('Qualified name of a class that will serve as template manager. Must fulfill WeBee\gCMS\Templates\TemplateManagerInterface')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->booleanNode('debug')
                    ->info('Decides if templates debugging is active or not')
                    ->defaultFalse()
                ->end()
                ->arrayNode('extensions')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()
                        ->info('Qualified name of a class with template extension')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function defineParsersConfiguration()
    {
        $treeBuilder = new TreeBuilder('parser');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('manager')
                    ->info('Qualified name of a class that will serve as parser manager. Must fulfill WeBee\gCMS\Parsers\ParserManagerInterface')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('parsers')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()
                        ->info('Qualified name of a class that will parse content. Must fulfill WeBee\gCMS\Parsers\ContentParserInterface')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function defineContentTypes()
    {
        $treeBuilder = new TreeBuilder('content');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('types')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()
                        ->info('Qualified name of a content type class. Must fulfill WeBee\gCMS\FlexContent\ContentInterface')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
