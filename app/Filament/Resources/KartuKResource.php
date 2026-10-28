<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KartuKResource\Pages;
use App\Imports\KartuKImport;
use App\Models\KartuK;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class KartuKResource extends Resource
{
    protected static ?string $model = KartuK::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Kartu';
    protected static ?string $label = 'Kartu K';
    protected static ?string $pluralLabel = 'Kartu K';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor')
                    ->required()
                    ->numeric()
                    ->unique(ignoreRecord: true)
                    ->live()                          // ← Tambahkan ini
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if (filled($state)) {
                            $set('qr_code', (string) $state);
                        }
                    }),

                Forms\Components\TextInput::make('qr_code')
                    ->label('QR Code')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->dehydrated()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('generate_qr')
                            ->label('Generate')
                            ->icon('heroicon-o-qr-code')
                            ->action(function (Forms\Set $set, Forms\Get $get, $livewire) {
                                $nomor = $get('nomor');

                                if (filled($nomor)) {
                                    $set('qr_code', (string) $nomor);

                                    // Optional: langsung simpan jika sedang edit
                                    if ($livewire->record) {
                                        $livewire->record->update(['qr_code' => (string) $nomor]);
                                        Notification::make()
                                            ->title('QR Code disimpan')
                                            ->success()
                                            ->send();
                                    }
                                } else {
                                    Notification::make()
                                        ->title('Isi Nomor terlebih dahulu')
                                        ->warning()
                                        ->send();
                                }
                            })
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->searchable()
                    ->sortable(),

              Tables\Columns\TextColumn::make('qr_code')
    ->label('QR Code')
    ->searchable()
    ->sortable()
    ->formatStateUsing(function ($state) {
        if (empty($state)) {
            return '<span class="text-xs text-gray-400 italic">Belum digenerate</span>';
        }
        $svg = base64_encode(QrCode::size(60)->generate($state));
        return '<div class="flex items-center gap-2">
                    <img src="data:image/svg+xml;base64,' . $svg . '" width="60" height="60" />
                    <span class="text-xs text-gray-400 font-mono">' . $state . '</span>
                </div>';
    })
    ->html(), // ← wajib agar HTML dirender

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('generate_all_qr')
    ->label('Generate Semua QR')
    ->icon('heroicon-o-qr-code')
    ->color('info')
    ->requiresConfirmation()
    ->modalHeading('Generate QR Code untuk Semua Kartu K')
    ->modalDescription('Tindakan ini akan mengisi kolom qr_code untuk semua kartu yang belum memilikinya.')
    ->modalSubmitActionLabel('Ya, Generate Sekarang')
    ->action(function () {
        $query = KartuK::where(function ($q) {
            $q->whereNull('qr_code')->orWhere('qr_code', '');
        });

        $total = $query->count();

        if ($total === 0) {
            Notification::make()
                ->title('Semua kartu sudah memiliki QR Code')
                ->info()
                ->send();
            return;
        }

        $count = 0;
        $query->chunkById(200, function ($kartuList) use (&$count) {
            foreach ($kartuList as $kartu) {
                $kartu->update([
                    'qr_code' => (string) $kartu->nomor,
                ]);
                $count++;
            }
        });

        Notification::make()
            ->title("✅ Berhasil generate {$count} QR Code")
            ->success()
            ->send();
    }),
                Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Pilih File Excel')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->preserveFilenames()
                            ->storeFiles(false),
                    ])
                    ->modalHeading('Import Kartu K dari Excel')
                    ->modalDescription('Pastikan file Excel memiliki kolom: nomor')
                    ->modalSubmitActionLabel('Import Sekarang')
                    ->action(function (array $data) {
                        try {
                            /** @var UploadedFile $file */
                            $file = $data['file'];

                            $import = new KartuKImport();
                            Excel::import($import, $file->getRealPath() ?: $file->path());

                            $imported = $import->getImportedCount();
                            $skipped  = $import->getSkippedCount();

                            Notification::make()
                                ->title('✅ Import selesai')
                                ->body("Berhasil: {$imported} data | Dilewati (duplikat/kosong): {$skipped} data")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('❌ Import gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Action::make('generate_qr')
                    ->label('Generate QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning')
                    ->visible(fn (KartuK $record) => empty($record->qr_code))
                    ->action(function (KartuK $record) {
                        $record->update([
                            'qr_code' => (string) $record->nomor,
                        ]);

                        Notification::make()
                            ->title('✅ QR Code berhasil di-generate')
                            ->success()
                            ->send();
                    }),

                Action::make('preview_qr')
                    ->label('Preview QR')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn (KartuK $record) => filled($record->qr_code))
                    ->modalHeading(fn (KartuK $record) => 'QR Code: ' . $record->nomor)
                    ->modalContent(fn (KartuK $record) => view('filament.modals.preview-qr', [
                        'qrCode' => QrCode::size(300)->generate($record->qr_code),
                        'label'  => $record->qr_code,
                    ]))
                    ->modalSubmitAction(false),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_generate_qr')
                        ->label('Generate QR Terpilih')
                        ->icon('heroicon-o-qr-code')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (empty($record->qr_code)) {
                                    $record->update([
                                        'qr_code' => (string) $record->nomor,
                                    ]);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title("✅ {$count} QR Code berhasil di-generate")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListKartuKs::route('/'),
            'create' => Pages\CreateKartuK::route('/create'),
            'edit'   => Pages\EditKartuK::route('/{record}/edit'),
        ];
    }
}