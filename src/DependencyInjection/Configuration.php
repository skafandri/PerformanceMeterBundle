<?php

namespace Skafandri\PerformanceMeterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('performance_meter');

        $root->children()->scalarNode('enabled')->defaultTrue();
        /** @var ArrayNodeDefinition $loggers */
        $loggers = $root->children()->arrayNode('loggers')->prototype('array');
        $loggers->children()->scalarNode('metric');

        return $tree;
    }
}
