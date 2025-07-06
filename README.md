# Cube Mage Paging Seal Bundle

一個為 Symfony 專案提供 PDF 騎縫章、蓋章等處理功能的 Bundle。

## 要求

* PHP 8.1+
* Symfony 6.0+

## 安裝

透過 Composer 安裝：
```bash
composer require cubemage/paging-seal-bundle
```
Symfony Flex 會自動在您的 `config/bundles.php` 中啟用此 Bundle。

## 設定 (可選)

如果您想自訂臨時檔案的儲存位置，請在 `config/packages/` 目錄下創建一個 `cube_mage_paging_seal.yaml` 檔案：

```yaml
# config/packages/cube_mage_paging_seal.yaml
cube_mage_paging_seal:
    # 預設路徑是 '%kernel.project_dir%/var/paging_seal'
    temp_path: '%kernel.project_dir%/var/my_sealed_docs'
```

## 如何使用

在您的 Controller 或任何其他服務中，直接注入 `CubeMage\PagingSealBundle\Service\PagingSealGenerator` 即可使用。

### Controller 使用範例

```php
<?php

namespace App\Controller;

use CubeMage\PagingSealBundle\Service\PagingSealGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

class MyPdfController extends AbstractController
{
    public function __construct(
        private PagingSealGenerator $sealGenerator // 自動注入服務
    ) {}
    
    #[Route('/my-app/process-pdf', name: 'process_my_pdf', methods: ['POST'])]
    public function processMyPdf(Request $request): BinaryFileResponse
    {
        $uploadedFile = $request->files->get('my_pdf_file');

        // ... 驗證上傳檔案的邏輯 ...
        if (!$uploadedFile) {
            throw new \Exception("No file uploaded.");
        }

        try {
            // 呼叫 Bundle 服務的核心方法，獲取生成後檔案的路徑
            $generatedPdfPath = $this->sealGenerator->process($uploadedFile->getPathname());

            // 將生成的檔案以二進位回應的形式，回傳給使用者下載
            // 設為 true 會在發送後自動刪除伺服器上的臨時檔案，非常方便
            return $this->file($generatedPdfPath, 'downloaded-file.pdf')->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // 處理服務中可能拋出的異常
            throw $this->createNotFoundException('PDF generation failed: ' . $e->getMessage());
        }
    }
}
```