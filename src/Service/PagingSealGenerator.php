<?php
// src/Service/PagingSealGenerator.php

declare(strict_types=1);

namespace CubeMage\PagingSealBundle\Service;

// 使用 FPDI 的 TCPDF 版本
use setasign\Fpdi\Tcpdf\Fpdi;

class PagingSealGenerator
{
    public function __construct(
        private string $pdfPath, // 透過依賴注入設定臨時檔案儲存路徑
        private ?string $defaultSealPath = null // 可選的預設公章路徑
    ) {
        if (!is_dir($this->pdfPath)) {
            mkdir($this->pdfPath, 0775, true);
        }
    }

    /**
     * 根據「整疊文件側面蓋章」原理，為PDF應用真正的騎縫章。
     *
     * @param string      $sourcePdfPath 來源PDF檔案路徑
     * @param string|null $sealImagePath 使用者上傳的印章圖片路徑 (可選)
     * @param string      $position      蓋章位置 ('right', 'left', 'top', 'bottom')
     * @param int         $sealSizeMM    印章的尺寸 (單位:mm)
     * @param float       $marginMM      印章距離頁面邊緣的距離 (單位:mm)
     * @return string                   返回最終生成PDF檔案的絕對路徑
     * @throws \Exception
     */
    public function generate(
        string $sourcePdfPath,
        ?string $sealImagePath = null,
        string $position = 'right',
        int $sealSizeMM = 30,
        float $marginMM = 5
    ): string {
        // 1. 決定使用的公章圖片路徑
        $finalSealPath = $sealImagePath ?: $this->defaultSealPath;
        if (!$finalSealPath || !is_readable($finalSealPath)) {
            throw new \InvalidArgumentException('公章圖片不存在或無法讀取。');
        }

        // 2. 初始化 TCPDF 版本的 FPDI
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // 3. 匯入所有原始頁面
        $totalPages = $pdf->setSourceFile($sourcePdfPath);
        for ($i = 1; $i <= $totalPages; $i++) {
            $templateId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        // 如果只有一頁或沒有頁面，則不蓋章，直接返回
        if ($totalPages < 2) {
            // 在這種情況下，我們可以選擇返回原始檔案或一個副本
            $outputPath = $this->pdfPath . '/' . bin2hex(random_bytes(20)) . '.pdf';
            copy($sourcePdfPath, $outputPath);
            return $outputPath;
        }

        // 4. 獲取圖片原始尺寸，計算長寬比
        list($originalWidth, $originalHeight) = getimagesize($finalSealPath);
        if ($originalWidth == 0 || $originalHeight == 0) {
            throw new \RuntimeException('無法讀取公章圖片尺寸。');
        }
        $aspectRatio = $originalWidth / $originalHeight;

        // 5. 根據蓋章位置，執行不同的繪製邏輯
        // (這裡完整地使用了您提供的、經過驗證的專業邏輯)
        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        switch ($position) {
            case 'left':
            case 'right':
                $totalSealWidth = $sealSizeMM;
                $totalSealHeight = $totalSealWidth / $aspectRatio;
                $sliceWidth = $totalSealWidth / $totalPages;
                $y_on_page = ($pageHeight - $totalSealHeight) / 2;
                $x_on_page = ($position == 'left') ? $marginMM : $pageWidth - $sliceWidth - $marginMM;

                for ($i = 1; $i <= $totalPages; $i++) {
                    $pdf->setPage($i);
                    $pdf->StartTransform();
                    $pdf->Rect($x_on_page, $y_on_page, $sliceWidth, $totalSealHeight, 'CNZ');
                    $imageX_offset = $x_on_page - (($i - 1) * $sliceWidth);
                    $pdf->Image($finalSealPath, $imageX_offset, $y_on_page, $totalSealWidth, $totalSealHeight, 'PNG');
                    $pdf->StopTransform();
                }
                break;

            case 'top':
            case 'bottom':
                $totalSealHeight = $sealSizeMM;
                $totalSealWidth = $totalSealHeight * $aspectRatio;
                $sliceHeight = $totalSealHeight / $totalPages;
                $x_on_page = ($pageWidth - $totalSealWidth) / 2;
                $clippingBoxY = ($position == 'top') ? $marginMM : $pageHeight - $sliceHeight - $marginMM;

                for ($i = 1; $i <= $totalPages; $i++) {
                    $pdf->setPage($i);
                    $pdf->StartTransform();
                    $pdf->Rect($x_on_page, $clippingBoxY, $totalSealWidth, $sliceHeight, 'CNZ');
                    $imageY_offset = $clippingBoxY - (($i - 1) * $sliceHeight);
                    $pdf->Image($finalSealPath, $x_on_page, $imageY_offset, $totalSealWidth, $totalSealHeight, 'PNG');
                    $pdf->StopTransform();
                }
                break;
        }

        // 6. 儲存檔案到由設定檔定義的臨時目錄
        $outputFileName = 'sealed-' . bin2hex(random_bytes(16)) . '.pdf';
        $outputPath = $this->pdfPath . '/' . $outputFileName;
        $pdf->Output($outputPath, 'F');

        return $outputPath;
    }
}
