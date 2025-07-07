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
            ->scalarNode('pdf_path')
            ->defaultValue('%kernel.project_dir%/var/cube-mage/paging-seal')
            ->info('The directory to store temporary processed PDF files.')
            ->end()
            // 新增：讓使用者可以設定預設公章的路徑
            ->scalarNode('default_seal_path')
            ->defaultNull()
            ->info('The absolute path to the default seal image.')
            ->end()
            ->end();

        return $treeBuilder;
    }
}