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
                    ->defaultValue('CoralRestUri')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('account')
                    ->defaultValue('CoralRestAccount')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('api_key')
                    ->defaultValue('CoralRestToken')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
