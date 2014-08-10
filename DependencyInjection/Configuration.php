<?php

namespace Coral\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('coral_core');

        $rootNode
            ->children()
                ->scalarNode('uri')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('account')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('api_key')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('version_file')
                    ->cannotBeEmpty()
                    ->defaultValue('%kernel.root_dir%/../version')
                    ->info('Path to the file containing version details.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
