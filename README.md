# CubeMage Paging Seal Bundle

一个为 Symfony 项目提供 PDF 骑缝章、盖章等处理功能的组件。

本组件的核心功能是提供一个可配置、可重用的服务，能够接收 PDF 文件，并以「骑缝章」或「单页盖章」的模式，专业地将印章图片叠加到PDF文档上。

## 系统要求

* PHP 8.1+
* Symfony 6.0+
* `gd` PHP 扩展（用于图像处理）

## 安装

#### 第一步：通过 Composer 安装

在您的 Symfony 项目根目录下，执行以下命令：

```bash
composer require cubemage/paging-seal-bundle
```

#### 第二步：启用 Bundle

如果您项目中启用了 Symfony Flex，这一步会自动完成。如果没有，请手动将以下这行加入到您项目的 `config/bundles.php` 文件中：

```php
// config/bundles.php
return [
    // ... 其他的 bundle
    CubeMage\PagingSealBundle\CubeMagePagingSealBundle::class => ['all' => true],
];
```

## 配置 (可选)

安装后，您可以选择性地在 `config/packages/` 目录下创建一个 `cube_mage_paging_seal.yaml` 文件，来覆写组件的默认配置。

```yaml
# config/packages/cube_mage_paging_seal.yaml
cube_mage_paging_seal:
    # 自定义您希望存放临时PDF文件的路径
    # 默认路径是: '%kernel.project_dir%/var/cubemage/paging-seal'
    pdf_path: '%kernel.project_dir%/var/cubemage/paging-seal'

    # 设置一个全局的、默认使用的公章图片的绝对路径
    # 如果不设置，则每次调用服务时都必须提供公章图片路径
    default_seal_path: '%kernel.project_dir%/assets/images/default_company_seal.png'
```

## 使用方法

本组件的核心是一个名为 `PagingSealGenerator` 的服务。在您的应用程序中（例如 Controller 或其他 Service），您只需要通过依赖注入来获取并使用它即可。

### Controller 使用示例

以下是一个完整的控制器示例，展示了如何创建一个 API 端点，它接收用户上传的PDF和可选的公章图片，处理后强制用户下载生成的新文件。

```php
<?php

namespace App\Controller;

// 1. 引入核心服务类
use CubeMage\PagingSealBundle\Service\PagingSealGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

class MyPdfController extends AbstractController
{
    // 2. 通过构造函数自动注入服务
    public function __construct(
        private PagingSealGenerator $sealGenerator
    ) {}
    
    #[Route('/api/contracts/process-seal', name: 'api_process_seal', methods: ['POST'])]
    public function processPdfAction(Request $request): BinaryFileResponse
    {
        // 3. 从请求中获取上传的文件
        /** @var UploadedFile|null $documentFile */
        $documentFile = $request->files->get('pdf');

        /** @var UploadedFile|null $sealFile */
        $sealFile = $request->files->get('seal_image'); // 这是可选的公章图片

        // 进行必要的檔案验证...
        if (!$documentFile || !$documentFile->isValid()) {
            throw new \InvalidArgumentException("必须上传有效的PDF文件。");
        }

        $sealPath = null;
        if ($sealFile && $sealFile->isValid()) {
            // 如果用户上传了自定义公章，使用它的临时路径
            $sealPath = $sealFile->getPathname();
        }
        
        try {
            // 4. 调用组件服务的核心方法来生成骑缝章PDF
            // 如果 $sealPath 为 null，服务会自动使用您在配置文件中配置的 default_seal_path
            $generatedPdfPath = $this->sealGenerator->generate(
                $documentFile->getPathname(), 
                $sealPath,
                'right', // 盖章位置: 'right', 'left', 'top', 'bottom'
                30,      // 印章尺寸 (mm)
                5        // 页边距 (mm)
            );

            // 5. 将生成的文件以二进制响应的形式，回传给用户下载
            // ->deleteFileAfterSend(true) 会在下载完成后自动删除服务器上的临时文件，非常方便
            return $this->file($generatedPdfPath, 'processed-document.pdf')->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // 在真实应用中，应该记录日志并返回一个更友好的错误响应
            throw $this->createNotFoundException('PDF处理失败: ' . $e->getMessage());
        }
    }
}
```

## 授权协议 (License)

本组件基于 MIT 协议发布。详情请见 `LICENSE` 文件。