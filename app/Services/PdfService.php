<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public static function generate($view, $data, $filename)
    {
        $pdf = Pdf::loadView($view, $data);
        return $pdf->download($filename);
    }
}
