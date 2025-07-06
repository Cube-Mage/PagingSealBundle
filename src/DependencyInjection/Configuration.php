<?php
// src/DependencyInjection/Configuration.php

declare(strict_types=1);

namespace CubeMage\PagingSealBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cube_mage_paging_seal');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('temp_path')
            ->info('The directory to store temporary processed PDF files.')
            ->defaultValue('%kernel.project_dir%/var/paging_seal') // 提供一個合理的預設值
            ->end()
            ->end();

        return $treeBuilder;
    }
}