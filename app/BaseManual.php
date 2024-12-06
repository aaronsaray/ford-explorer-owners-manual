<?php

declare(strict_types=1);

namespace App;

use Mpdf\Mpdf;

abstract class BaseManual
{
    abstract protected function urls(): array;

    abstract protected function title(): string;

    abstract protected function fileName(): string;

    protected function getContents(string $file): string
    {
        return str_replace(
            '{{ title }}',
            $this->title(),
            file_get_contents($file)
        );
    }

    public function createPdf(): void
    {
        error_reporting(E_ERROR);

        $mpdf = new Mpdf();
        $mpdf->setBasePath('https://www.foexplorer.com/');
        $mpdf->WriteHTML($this->getContents(__DIR__ . '/../resources/owners-manual-cover.html'));
        $mpdf->SetHTMLFooter('<div class="page">Page {PAGENO}</div>');
        $mpdf->WriteHTML($this->getContents(__DIR__ . '/../resources/first-page.html'));

        foreach ($this->urls() as $url) {
            print "Processing $url";

            $html = file_get_contents($url);
            $start = stripos($html, '<div align="center">');
            $end = stripos($html, '<div class="fodexp_rightblock">');

            $first = substr($html, 0, $start);
            $last = substr($html, $end);

            $html = $first . $last;

            $mpdf->WriteHTML($html);
            $mpdf->WriteHTML('<div class="break-after-this"></div>');
            print " Done.\n";
        }

        $file = $this->fileName();
        $mpdf->Output($file);

        print "File written to: {$file}\n\n";
    }
}