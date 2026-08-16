<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BerkasMagang extends Model
{
    use HasFactory;

    // Nama tabel yang dipakai di database
    protected $table = 'berkas_magang';
    
    // Primary key dari tabel ini
    protected $primaryKey = 'id_berkas_magang';
    
    // Karena di DB kamu ada created_at, tapi nggak ada updated_at
    // Jadi kolom updated_at dimatikan biar Laravel nggak nyari kolom itu pas data diubah
    const UPDATED_AT = null; 

    // Daftar kolom yang boleh diisi langsung lewat kode
    protected $fillable = [
        'nama_berkas',
        'jenis_berkas',
        'file',
        'deskripsi',
    ];
}