<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'jurusan';
    
    // Primary key-nya pakai id_jurusan
    protected $primaryKey = 'id_jurusan';
    
    // Id-nya auto-increment dan tipenya integer
    public $incrementing = true;
    protected $keyType = 'int';
    
    // Timestamps dimatiin
    public $timestamps = false;

    // Cuma bisa ngisi kode_jurusan dan nama_jurusan lewat kode
    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
    ];
}