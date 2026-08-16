<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'jabatan';
    
    // Primary key-nya pakai id_jabatan
    protected $primaryKey = 'id_jabatan';
    
    // Id-nya auto-increment
    public $incrementing = true;
    
    // Tipe data id-nya integer
    protected $keyType = 'int';
    
    // Dimatiin soalnya nggak pakai kolom waktu bawaan Laravel
    public $timestamps = false;

    // Cuma boleh ngisi kode_jabatan dan nama_jabatan lewat kode
    protected $fillable = [
        'kode_jabatan',
        'nama_jabatan',
    ];
}