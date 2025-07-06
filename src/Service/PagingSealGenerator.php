<?php
// src/Service/PagingSealGenerator.php

declare(strict_types=1);

namespace CubeMage\PagingSealBundle\Service;

use setasign\Fpdi\Fpdi;

class PagingSealGenerator
{
    // 使用建構函式屬性提升 (PHP 8.0+)
    public function __construct(
        private string $tempPath
    ) {
        // 確保臨時目錄存在且可寫
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0775, true);
        }
        if (!is_writable($this->tempPath)) {
            throw new \RuntimeException(sprintf('The temporary directory "%s" is not writable.', $this->tempPath));
        }
    }

    /**
     * 核心方法：處理PDF並返回新檔案路徑
     *
     * @param string $sourcePdfPath 來源PDF的絕對路徑
     * @return string 生成的新PDF檔案絕對路徑
     * @throws \InvalidArgumentException 如果來源檔案不存在
     * @throws \RuntimeException 如果PDF處理失敗
     */
    public function process(string $sourcePdfPath): string
    {
        if (!file_exists($sourcePdfPath) || !is_readable($sourcePdfPath)) {
            throw new \InvalidArgumentException("Source PDF file not found or is not readable at: {$sourcePdfPath}");
        }

        try {
            $pdf = new Fpdi();

            // --- 在這裡放入您所有的PDF處理邏輯 ---
            // 例如：計算頁數、判斷是單頁還是多頁、呼叫騎縫章或單頁蓋章邏輯
            // 以下為一個簡單的複製範例

            $pageCount = $pdf->setSourceFile($sourcePdfPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
            // ------------------------------------

            $newFilename = bin2hex(random_bytes(20)) . '.pdf';
            $outputPath = $this->tempPath . '/' . $newFilename;

            $pdf->Output('F', $outputPath);

            return $outputPath;

        } catch (\Exception $e) {
            // 記錄詳細錯誤日誌是好習慣
            // logger->error('PDF processing failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to process PDF file.', 0, $e);
        }
    }
}