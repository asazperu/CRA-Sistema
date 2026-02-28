<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use RuntimeException;

final class ExportService
{
    public function exportPdf(string $title, string $htmlContent, string $fileName): string
    {
        $wrapped = $this->wrapHtml($title, $htmlContent);
        $fullPath = base_path('storage/exports/' . $fileName);

        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($wrapped, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            file_put_contents($fullPath, $dompdf->output());
            return $fullPath;
        }

        if (class_exists('Mpdf\\Mpdf')) {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($wrapped);
            $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);
            return $fullPath;
        }

        throw new RuntimeException('No se encontró Dompdf ni mPDF. Instale una de estas librerías para exportar PDF.');
    }

    public function exportDocx(string $title, string $plainText, string $fileName): string
    {
        if (!class_exists('PhpOffice\\PhpWord\\PhpWord')) {
            throw new RuntimeException('No se encontró PhpWord. Instale phpoffice/phpword para exportar DOCX.');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText($title, ['bold' => true, 'size' => 14]);

        $settings = new Setting();
        $brandName = $settings->get('brand_name', 'Castro Romero Abogados') ?? 'Castro Romero Abogados';
        $section->addText($brandName, ['italic' => true, 'size' => 10]);
        $section->addTextBreak(1);

        foreach (explode("\n", $plainText) as $line) {
            $section->addText($line);
        }

        $fullPath = base_path('storage/exports/' . $fileName);
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);
        return $fullPath;
    }

    private function wrapHtml(string $title, string $content): string
    {
        $settings = new Setting();
        $brandName = htmlspecialchars((string) ($settings->get('brand_name', 'Castro Romero Abogados') ?? 'Castro Romero Abogados'), ENT_QUOTES, 'UTF-8');
        $logo = htmlspecialchars((string) ($settings->get('brand_logo', '') ?? ''), ENT_QUOTES, 'UTF-8');

        $headerLogo = $logo !== '' ? '<img src="' . $logo . '" style="height:42px;">' : '';

        return '<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;} .head{border-bottom:1px solid #888;padding-bottom:8px;margin-bottom:12px;} h1{font-size:18px;margin:6px 0;} .brand{font-size:11px;color:#444;}</style></head><body>'
            . '<div class="head">' . $headerLogo . '<div class="brand">' . $brandName . '</div></div>'
            . '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
            . $content
            . '</body></html>';
    }
}
