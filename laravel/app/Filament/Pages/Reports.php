<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\BarangMasuk;
use App\Models\BarangMasukItem;
use App\Models\PurchaseOrder;
use App\Models\Production;
use Filament\Actions\Action;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Pusat';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.reports';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('download_monthly_summary')
                ->label('Download Ringkasan Bulanan (PDF)')
                ->action(function () {
                    $month = now()->format('F Y');
                    $totalNilaiPoBulanIni = (float) PurchaseOrder::query()
                        ->whereMonth('date', now()->month)
                        ->sum('total_amount');

                    $data = [
                        'title' => "Ringkasan Bulanan - {$month}",
                        'headers' => ['Kategori', 'Total'],
                        'rows' => [
                            ['Total PO Aktif', PurchaseOrder::whereIn('stage', ['barang_datang', 'qc', 'produksi', 'packing', 'pengiriman'])->count()],
                            ['Total PO Done', PurchaseOrder::where('stage', 'done')->count()],
                            ['Barang Pending QC', BarangMasukItem::where('qc_status', 'pending')->count()],
                            ['Total Produksi', Production::whereMonth('date', now()->month)->count()],
                            ['Total Nilai PO (Bulan Ini)', 'Rp ' . number_format($totalNilaiPoBulanIni, 0, ',', '.')],
                        ],
                    ];
                    return response()->streamDownload(function () use ($data) {
                        echo \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.generic', $data)->output();
                    }, "Ringkasan-{$month}.pdf");
                }),
        ];
    }
}
