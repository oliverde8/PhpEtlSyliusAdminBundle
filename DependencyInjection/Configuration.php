<?php

namespace Oliverde8\PhpEtlSyliusAdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('php_etl_sylius_admin');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('resource')->end();

        return $treeBuilder;
    }
}
