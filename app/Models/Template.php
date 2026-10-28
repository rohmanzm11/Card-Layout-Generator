<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;


    protected $table = 'templates';
    protected $fillable = ['nama_batch', 'gambar'];

    public function printKarts()
    {
        return $this->hasMany(PrintKartu::class);
    }
}