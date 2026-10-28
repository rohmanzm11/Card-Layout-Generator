<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrintKartuResource\Pages;
use App\Models\KartuB;
use App\Models\KartuK;
use App\Models\PrintKartu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 
use Barryvdh\DomPDF\Facade\Pdf;

class PrintKartuResource extends Resource
{
    protected static ?string $model = PrintKartu::class;

    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationGroup = 'Printing';
    protected static ?string $label = 'Print Kartu';
    protected static ?string $pluralLabel = 'Print Kartu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── Pilihan Jenis Kartu ──────────────────────────────────────
                Forms\Components\Radio::make('jenis_kartu')
                    ->label('Jenis Kartu')
                    ->options([
                        'K' => 'Kartu K',
                        'B' => 'Kartu B',
                    ])
                    ->required()
                    ->inline()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        // Reset pilihan kartu saat jenis berubah
                        $set('kartu_k_s_id', null);
                        $set('kartu_b_s_id', null);
                        $set('range_start', null);
                        $set('range_end', null);
                    }),

              // ── Kartu K (hanya tampil jika jenis = K) ───────────────────
Forms\Components\Select::make('kartu_k_s_id')
    ->label('Kartu K')
    ->options(fn() => KartuK::pluck('qr_code', 'id'))
    ->searchable()
    ->preload()
    ->visible(fn (Get $get) => $get('jenis_kartu') === 'K')
    ->required(fn (Get $get) => $get('jenis_kartu') === 'K')
    ->live()
    ->helperText('Data QR Code akan diambil dari kolom qr_code kartu ini'),


Forms\Components\TextInput::make('jumlah')
    ->label('Jumlah Kartu')
    ->numeric()
    ->minValue(1)
    ->maxValue(5040)
    ->default(1)
    ->required(fn (Get $get) => $get('jenis_kartu') === 'K')
    ->visible(fn (Get $get) => $get('jenis_kartu') === 'K')
    ->suffix('kartu')
    ->helperText('Jumlah kartu yang akan diprint mulai dari nomor awal'),


              // ── Kartu B (hanya tampil jika jenis = B) ───────────────────
Forms\Components\Select::make('kartu_b_s_id')
    ->label('Kartu B')
    ->options(fn() => KartuB::pluck('qr_code', 'id'))
    ->searchable()
    ->preload()
    ->visible(fn (Get $get) => $get('jenis_kartu') === 'B')
    ->required(fn (Get $get) => $get('jenis_kartu') === 'B')
    ->live()
    ->helperText('Kartu pertama yang akan diprint (urut berdasarkan nomor)'),
                 Forms\Components\TextInput::make('jumlah')
                    ->label('Jumlah Kartu')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5000)
                    ->default(1)
                    ->required(fn (Get $get) => $get('jenis_kartu') === 'B')
                    ->visible(fn (Get $get) => $get('jenis_kartu') === 'B')
            ->suffix('kartu')
            ->helperText('Jumlah kartu yang akan diprint dari data pertama'),

                // ── Template (gambar diambil dari sini) ─────────────────────
                Forms\Components\Select::make('template_id')
                    ->label('Template')
                    ->relationship('template', 'nama_batch')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Gambar kartu akan diambil dari tabel template'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_kartu')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'K' => 'success',
                        'B' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                // Tables\Columns\TextColumn::make('kartuK.nomor')
                //     ->label('Kartu K')
                //     ->searchable()
                //     ->placeholder('-'),

                // Tables\Columns\TextColumn::make('kartuB.nomor')
                //     ->label('Kartu B')
                //     ->searchable()
                //     ->placeholder('-'),

                 Tables\Columns\TextColumn::make('nomor_awal')
        ->label('Nomor Awal')
        ->state(function ($record) {
            if ($record->jenis_kartu === 'K') {
                return $record->kartuK?->nomor ?? '-';
            }

            if ($record->jenis_kartu === 'B') {
                return $record->kartuB?->nomor ?? '-';
            }

            return '-';
        }),

                 Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->suffix(' kartu')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_kartu')
                    ->label('Jenis Kartu')
                    ->options([
                        'K' => 'Kartu K',
                        'B' => 'Kartu B',
                    ]),

                Tables\Filters\SelectFilter::make('template_id')
                    ->relationship('template', 'nama_batch')
                    ->label('Template'),
            ])
            ->actions([
            
                Tables\Actions\Action::make('loadview')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('print-kartu.download', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrintKartus::route('/'),
            'create' => Pages\CreatePrintKartu::route('/create'),
            'edit'   => Pages\EditPrintKartu::route('/{record}/edit'),
        ];
    }
}