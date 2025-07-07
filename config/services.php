<?php
// PagingSealBundle/config/services.php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CubeMage\PagingSealBundle\Service\PagingSealGenerator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    // 關鍵：將服務的主要ID設定為它的完整類別名稱 (FQCN)
    $services->set(PagingSealGenerator::class)
        ->public() // 設為 public 確保可被存取
        ->arg('$tempPath', '%cubemage_paging_seal.temp_path%')
        ->arg('$defaultSealPath', '%cubemage_paging_seal.default_seal_path%');

    // 為了兼容舊的獲取方式，我們仍然可以設定一個別名
    $services->alias('cubemage_paging_seal.generator', PagingSealGenerator::class);
};
