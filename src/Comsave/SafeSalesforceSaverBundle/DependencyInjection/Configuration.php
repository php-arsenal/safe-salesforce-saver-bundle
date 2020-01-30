<?php

namespace Comsave\SafeSalesforceSaver\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder("SafeSalesforceSaver");
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('rabbitmq')->isRequired()
                    ->children()
                        ->scalarNode('url')->isRequired()->end()
                        ->scalarNode('vhost')->isRequired()->end()
                        ->scalarNode('username')->isRequired()->end()
                        ->scalarNode('password')->isRequired()->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}