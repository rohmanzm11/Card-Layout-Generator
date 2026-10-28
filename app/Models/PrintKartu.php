<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintKartu extends Model
{
    use HasFactory;

    protected $table = 'print_kartus';

    // PERBAIKAN: Sesuaikan dengan nama kolom di database (_s_id)
    protected $fillable = [
        'jenis_kartu',
        'kartu_k_s_id',
        'kartu_b_s_id',
        // 'range_start',
        // 'range_end',
        'jumlah', 
        'template_id',
    ];

    /**
     * Ambil qr_code dari KartuK berdasarkan range nomor
     */
    public function getQrCodesAttribute(): array
    {
        // PERBAIKAN: Gunakan kartu_k_s_id
        if ($this->jenis_kartu !== 'K' || !$this->kartu_k_s_id) {
            return [];
        }

        // Memastikan range_start dan range_end diisi sebelum query
        if (is_null($this->range_start) || is_null($this->range_end)) {
            return [];
        }

        return KartuK::whereBetween('nomor', [$this->range_start, $this->range_end])
            ->pluck('qr_code')
            ->toArray();
    }

    /**
     * Relasi ke model KartuK
     */
    public function kartuK(): BelongsTo
{
    return $this->belongsTo(KartuK::class, 'kartu_k_s_id', 'id');
}

    /**
     * Relasi ke model KartuB
     */
    public function kartuB(): BelongsTo
    {
        // PERBAIKAN: Set foreign key kedua menjadi 'kartu_b_s_id'
        return $this->belongsTo(KartuB::class, 'kartu_b_s_id');
    }

    /**
     * Relasi ke model Template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
}