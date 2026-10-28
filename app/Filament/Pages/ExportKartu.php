<?php

namespace App\Filament\Pages;

use App\Models\KartuB;
use App\Models\KartuK;
use App\Models\PrintKartu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;

class ExportKartu extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel = 'Export Kartu';
    protected static ?string $title = 'Export Kartu';
    protected static ?string $navigationGroup = 'Printing';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.export-kartu';

    // State form kiri (Kartu K)
    public ?array $dataK = [];

    // State form kanan (Kartu B)
    public ?array $dataB = [];

    public function mount(): void
    {
        $this->formK->fill([
            'nomor_awal'  => null,
            'jumlah'      => 1,
            'template_id' => null,
        ]);

        $this->formB->fill([
            'nomor_awal'  => null,
            'jumlah'      => 1,
            'template_id' => null,
        ]);
    }

    protected function getForms(): array
    {
        return [
            'formK',
            'formB',
        ];
    }

  // ── Form Kiri : Kartu K ───────────────────────────────────────
public function formK(Form $form): Form
{
    return $form
        ->schema([
            Section::make()
                ->schema([
                    Select::make('nomor_awal')
                        ->label('Nomor Awal')
                        ->options(fn () => KartuK::orderBy('nomor')->pluck('nomor', 'nomor'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Pilih nomor kartu K yang menjadi titik mulai'),

                    TextInput::make('jumlah')
                        ->label('Jumlah')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(5040)
                        ->default(1)
                        ->required()
                        ->suffix('kartu')
                        ->helperText('Jumlah kartu yang akan dicetak mulai dari nomor awal'),

                    Select::make('template_id')
                        ->label('Template')
                        ->options(fn () => \App\Models\Template::query()->pluck('nama_batch', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Gambar kartu diambil dari template ini'),
                ])
                ->columns(1),
        ])
        ->statePath('dataK');
}

// ── Form Kanan : Kartu B ──────────────────────────────────────
public function formB(Form $form): Form
{
    return $form
        ->schema([
            Section::make()
                ->schema([
                    Select::make('nomor_awal')
                        ->label('Nomor Awal')
                        ->options(fn () => KartuB::orderBy('nomor')->pluck('nomor', 'nomor'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Pilih nomor kartu B yang menjadi titik mulai'),

                    TextInput::make('jumlah')
                        ->label('Jumlah')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(5000)
                        ->default(1)
                        ->required()
                        ->suffix('kartu')
                        ->helperText('Jumlah kartu yang akan dicetak mulai dari nomor awal'),

                    Select::make('template_id')
                        ->label('Template')
                        ->options(fn () => \App\Models\Template::query()->pluck('nama_batch', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Gambar kartu diambil dari template ini'),
                ])
                ->columns(1),
        ])
        ->statePath('dataB');
}
    // ── Action Export Kartu K ─────────────────────────────────────
    public function exportKartuK(): void
    {
        $data = $this->formK->getState();

        $startNomor = $data['nomor_awal'];
        $jumlah     = (int) $data['jumlah'];
        $templateId = $data['template_id'];

        $kartu = KartuK::where('nomor', '>=', $startNomor)
            ->orderBy('nomor')
            ->first();

        if (!$kartu) {
            Notification::make()
                ->title('Gagal')
                ->body('Kartu K dengan nomor tersebut tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        $record = PrintKartu::create([
            'jenis_kartu'  => 'K',
            'kartu_k_s_id' => $kartu->id,
            'jumlah'       => $jumlah,
            'template_id'  => $templateId,
        ]);

        Notification::make()
            ->title('Berhasil')
            ->body("Export Kartu K dimulai dari nomor {$startNomor} sebanyak {$jumlah} kartu.")
            ->success()
            ->send();

        $this->redirect(route('print-kartu.download', $record));
    }

    // ── Action Export Kartu B ─────────────────────────────────────
    public function exportKartuB(): void
    {
        $data = $this->formB->getState();

        $startNomor = $data['nomor_awal'];
        $jumlah     = (int) $data['jumlah'];
        $templateId = $data['template_id'];

        $kartu = KartuB::where('nomor', '>=', $startNomor)
            ->orderBy('nomor')
            ->first();

        if (!$kartu) {
            Notification::make()
                ->title('Gagal')
                ->body('Kartu B dengan nomor tersebut tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        $record = PrintKartu::create([
            'jenis_kartu'  => 'B',
            'kartu_b_s_id' => $kartu->id,
            'jumlah'       => $jumlah,
            'template_id'  => $templateId,
        ]);

        Notification::make()
            ->title('Berhasil')
            ->body("Export Kartu B dimulai dari nomor {$startNomor} sebanyak {$jumlah} kartu.")
            ->success()
            ->send();

        $this->redirect(route('print-kartu.download', $record));
    }
}