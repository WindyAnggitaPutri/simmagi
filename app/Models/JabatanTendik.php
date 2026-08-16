<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JabatanTendik extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'jabatan_tendik';
    
    // Primary key-nya
    protected $primaryKey = 'id_jabatan_tendik';
    
    // Id-nya auto-increment dan tipenya integer
    public $incrementing = true;
    protected $keyType = 'int';
    
    // Timestamps dimatiin
    public $timestamps = false;

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'id_dosen',
        'id_pegawai', // TAMBAHKAN INI
        'id_jabatan',
        'ttd_digital',
    ];

    // Relasi ke tabel dosen, artinya jabatan ini bisa dipegang oleh dosen
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }

    // Relasi ke tabel pegawai, jabatan ini juga bisa dipegang oleh pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

    // Relasi ke tabel jabatan untuk ngambil detail nama jabatannya
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }
    
    // Helper: Ambil data orang (entah dosen atau pegawai) untuk tampilan
    // Fungsinya buat ngecek, kalau dosen-nya ada ya ambil data dosen, tapi kalau nggak ada ambil data pegawai
    public function getPersonAttribute()
    {
        return $this->dosen ?? $this->pegawai;
    }
}