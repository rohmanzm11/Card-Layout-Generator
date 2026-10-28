<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuB extends Model
{
    use HasFactory;

    protected $table = 'kartu_b_s';

    protected $fillable = ['nomor', 'qr_code'];
    
    protected $casts = [
        'nomor' => 'integer',
    ];

    public function setQrCodeAttribute($value)
    {
        $this->attributes['qr_code'] = $value ? (string) $value : null;
    }

    public function printKarts()
    {
        return $this->hasMany(PrintKartu::class);
    }
}