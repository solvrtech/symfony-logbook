<?php

namespace Solvrtech\Symfony\Logbook\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     * 
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('logbook');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('api')
            ->children()
            ->scalarNode('url')->end()
            ->scalarNode('key')->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
