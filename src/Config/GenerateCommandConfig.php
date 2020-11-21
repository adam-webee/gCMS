<?php

declare(strict_types=1);

namespace WeBee\gCMS\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class GenerateCommandConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('main');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('branch_name')
                    ->info('Branch name to generate from')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('master')
                ->end()
                ->scalarNode('repository_folder')
                    ->info('Path to git repository folder')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('output_folder')
                    ->info('Path to location where generated files will be stored')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('templates_folder')
                    ->info('Path to location with twig templates')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
