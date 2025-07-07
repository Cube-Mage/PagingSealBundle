<?php
// src/DependencyInjection/CubeMagePagingSealExtension.php

declare(strict_types=1);

namespace CubeMage\PagingSealBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CubeMagePagingSealExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('cubemage_paging_seal.temp_path', $config['temp_path']);
        $container->setParameter('cubemage_paging_seal.default_seal_path', $config['default_seal_path']);
    }

    public function getAlias(): string
    {
        return 'cube_mage_paging_seal';
    }
}