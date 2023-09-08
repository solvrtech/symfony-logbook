<?php

namespace Solvrtech\Logbook\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
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
            ->scalarNode('url')->isRequired()->end()
            ->scalarNode('key')->isRequired()->end()
            ->end()
            ->end()
            ->scalarNode('instance_id')
            ->defaultValue('default')
            ->validate()
            ->ifTrue(fn($value) => strlen($value) < 5 || strlen($value) > 20)
            ->thenInvalid('The instance_id must be between 5 and 20 characters long.')
            ->end()
            ->end()
            ->scalarNode('transport')
            ->defaultValue('sync://')
            ->end();

        return $treeBuilder;
    }
}
