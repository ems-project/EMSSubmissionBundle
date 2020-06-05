<?php

namespace EMS\SubmissionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ems_submission');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('default_timeout')->defaultValue('10')->end()
                ->variableNode('connections')
                    ->example('[{"connection": "conn-id", "user": "your-username": "password": "your-password"}]')
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
}
