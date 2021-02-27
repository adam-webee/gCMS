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
                ->arrayNode('input')
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
                ->end()
                ->arrayNode('output')
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
                ->end()
                ->arrayNode('resources')
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
                ->end()
                ->scalarNode('name')
                    ->info('Generated content name e.g. blogs name or page name')
                    ->defaultValue('')
                ->end()
                ->scalarNode('slogan')
                    ->info('Generated content slogan')
                    ->defaultValue('')
                ->end()
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
}
