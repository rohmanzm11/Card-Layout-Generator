<x-filament-panels::page>
    <style>
        .export-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .export-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .export-card {
            background-color: rgb(255 255 255);
            border-radius: 0.75rem;
            border: 1px solid rgb(229 231 235);
            padding: 1.5rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }

        .dark .export-card {
            background-color: rgb(31 41 55);
            border-color: rgb(55 65 81);
        }

        .export-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgb(229 231 235);
        }

        .dark .export-header {
            border-color: rgb(55 65 81);
        }

        .export-header-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-k {
            background-color: rgb(220 252 231);
            color: rgb(22 163 74);
        }

        .dark .icon-k {
            background-color: rgb(20 83 45);
            color: rgb(74 222 128);
        }

        .icon-b {
            background-color: rgb(219 234 254);
            color: rgb(37 99 235);
        }

        .dark .icon-b {
            background-color: rgb(30 58 138);
            color: rgb(96 165 250);
        }

        .export-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: rgb(17 24 39);
        }

        .dark .export-title {
            color: rgb(243 244 246);
        }

        .export-desc {
            font-size: 0.875rem;
            color: rgb(107 114 128);
        }

        .dark .export-desc {
            color: rgb(156 163 175);
        }

        .btn-export {
            margin-top: 1.25rem;
            width: 100%;
        }
    </style>

    <div class="export-grid">
        {{-- ==================== KIRI : KARTU K ==================== --}}
        <div class="export-card">
            <div class="export-header">
                <div class="export-header-icon icon-k">
                    <x-heroicon-o-identification class="w-6 h-6" />
                </div>
                <div>
                    <div class="export-title">Export Kartu K</div>
                    <div class="export-desc">Nomor awal + jumlah kartu</div>
                </div>
            </div>

            <form wire:submit="exportKartuK">
                {{ $this->formK }}

                <div class="btn-export">
                    <x-filament::button type="submit" color="success" size="lg" class="w-full" icon="heroicon-o-printer">
                        Export Kartu K
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- ==================== KANAN : KARTU B ==================== --}}
        <div class="export-card">
            <div class="export-header">
                <div class="export-header-icon icon-b">
                    <x-heroicon-o-credit-card class="w-6 h-6" />
                </div>
                <div>
                    <div class="export-title">Export Kartu B</div>
                    <div class="export-desc">Nomor awal + jumlah kartu</div>
                </div>
            </div>

            <form wire:submit="exportKartuB">
                {{ $this->formB }}

                <div class="btn-export">
                    <x-filament::button type="submit" color="info" size="lg" class="w-full" icon="heroicon-o-printer">
                        Export Kartu B
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>