<?php
// config/services.php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CubeMage\PagingSealBundle\Service\PagingSealGenerator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    // 註冊我們的核心服務
    $services->set(PagingSealGenerator::class)
        ->public() // 將服務設為 public，方便使用者在舊版Symfony或特殊情況下獲取
        ->arg('$tempPath', '%cubemage_paging_seal.temp_path%'); // 綁定參數

    // 建立一個別名，讓使用者可以透過類別名稱直接注入
    $services->alias(PagingSealGenerator::class, 'cubemage_paging_seal.generator');
};