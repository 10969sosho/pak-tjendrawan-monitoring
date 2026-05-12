<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @livewire(\App\Filament\Widgets\StatsOverview::class)
    </div>
    
    <div class="mt-8">
        <h3 class="text-lg font-medium">Laporan Tersedia</h3>
        <p class="text-sm text-gray-500">Gunakan tombol export di masing-masing modul untuk laporan detail, atau gunakan tombol di atas untuk ringkasan bulanan.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
            <x-filament::card>
                <h4 class="font-bold">Laporan PO</h4>
                <p>Semua PO, status tracking, customer, qty, deadline.</p>
                <div class="mt-4">
                    <x-filament::button href="/admin/purchase-orders" tag="a" color="info">Ke PO</x-filament::button>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <h4 class="font-bold">Laporan Produksi</h4>
                <p>Hasil produksi per PO dan barang masuk.</p>
                <div class="mt-4">
                    <x-filament::button href="/admin/productions" tag="a" color="info">Ke Produksi</x-filament::button>
                </div>
            </x-filament::card>

            <x-filament::card>
                <h4 class="font-bold">Laporan Packing</h4>
                <p>Total pack, sisa pcs, dan catatan packing.</p>
                <div class="mt-4">
                    <x-filament::button href="/admin/packings" tag="a" color="info">Ke Packing</x-filament::button>
                </div>
            </x-filament::card>

            <x-filament::card>
                <h4 class="font-bold">Laporan QC</h4>
                <p>Barang pending QC / lolos / reject.</p>
                <div class="mt-4">
                    <x-filament::button href="/admin/barang-masuks" tag="a" color="info">Ke Barang Masuk</x-filament::button>
                </div>
            </x-filament::card>

            <x-filament::card>
                <h4 class="font-bold">Laporan Pengiriman</h4>
                <p>Status pengiriman per PO.</p>
                <div class="mt-4">
                    <x-filament::button href="/admin/pengirimans" tag="a" color="info">Ke Pengiriman</x-filament::button>
                </div>
            </x-filament::card>
        </div>
    </div>
</x-filament-panels::page>
