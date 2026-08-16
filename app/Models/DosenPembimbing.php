<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenPembimbing extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'dosen_pembimbing';
    
    // Primary key-nya
    protected $primaryKey = 'id_dosen_pembimbing';
    
    // Id-nya auto-increment
    public $incrementing = true;
    
    // Tipe id-nya integer
    protected $keyType = 'int';
    
    // Timestamps dimatiin
    public $timestamps = false;

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'id_dosen',
        'id_mahasiswa',
        'id_pengajuan',
        'file_sk',
        'catatan',
    ];

    // Relasi ke tabel dosen, data pembimbing ini milik satu dosen
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    // Relasi ke tabel mahasiswa, data pembimbing ini untuk satu mahasiswa
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    // Relasi ke tabel pengajuan magang
    public function pengajuanMagang()
    {
        return $this->belongsTo(PengajuanMagang::class, 'id_pengajuan', 'id_pengajuan');
    }
    
    // Relasi ke tabel logbook, satu dosen pembimbing bisa ngisi banyak logbook
    public function logbooks()
    {
        return $this->hasMany(Logbook::class, 'id_dosen_pembimbing', 'id_dosen_pembimbing');
    }
}