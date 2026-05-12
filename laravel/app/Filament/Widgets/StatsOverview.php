<?php

namespace App\Filament\Widgets;

use App\Models\BarangMasuk;
use App\Models\PurchaseOrder;
use App\Models\Production;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    protected function getStats(): array
    {
        $poAktif = PurchaseOrder::query()
            ->whereIn('stage', ['deal', 'barang_datang', 'qc', 'produksi', 'packing', 'pengiriman'])
            ->count();

        $produksiBerjalan = Production::query()
            ->where('status', 'draft')
            ->count();

        $poSelesai = PurchaseOrder::query()
            ->where('stage', 'done')
            ->count();

        $barangPendingQc = BarangMasuk::query()
            ->where('qc_status', 'pending')
            ->count();

        $barangSiapKirim = PurchaseOrder::query()
            ->where('stage', 'packing')
            ->count();

        return [
            Stat::make('Total PO Aktif', $poAktif)
                ->description('Belum Done')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make('Total Produksi Berjalan', $produksiBerjalan)
                ->description('Status: Proses')
                ->descriptionIcon('heroicon-m-cog')
                ->color('warning'),
            Stat::make('Total Selesai', $poSelesai)
                ->description('PO Done')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Barang Pending QC', $barangPendingQc)
                ->description('QC: Pending')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),
            Stat::make('Barang Siap Kirim', $barangSiapKirim)
                ->description('Sudah Packing')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('info'),
        ];
    }
}
