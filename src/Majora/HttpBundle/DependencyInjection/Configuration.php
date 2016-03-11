<?php

namespace Majora\HttpBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('majora_http');

        $rootNode
            ->children()
                ->arrayNode('clients')
                    ->isRequired()
                        ->requiresAtLeastOneElement()
                        ->useAttributeAsKey('id', false)
                        ->prototype('array')
                            ->children()
                                ->scalarNode('base_uri')->defaultValue("")->end()
                                    ->arrayNode('headers')
                                        ->children()
                                            ->scalarNode('content_type')->end()
                                            ->scalarNode('api_key')->end()
                                        ->end()
                                    ->end()
                                ->scalarNode('body')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;

        return $treeBuilder;
    }
}
