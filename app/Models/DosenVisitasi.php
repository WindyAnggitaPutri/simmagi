<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenVisitasi extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'dosen_visitasi';
    
    // Primary key-nya
    protected $primaryKey = 'id_dosen_visitasi';

    // Timestamps dimatiin
    public $timestamps = false;

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'id_dosen',
        'id_mahasiswa',
        'id_perusahaan',
        'id_dosen_pembimbing',
        'tahun',
        // 'status',        // ✅ TAMBAH
        // 'file_sk',
        'tgl_visitasi',
        'foto',
        'catatan',
    ];

    // Ngubah tipe data pas diambil dari database
    protected $casts = [
        'tahun' => 'integer', // tahun diubah jadi angka bulat
        'tgl_visitasi' => 'date', // tanggal visitasi diubah jadi format date
    ];

    // Relasi ke dosen yang melakukan visitasi
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    // Relasi ke mahasiswa yang dikunjungi
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    // Relasi ke perusahaan tempat visitasi
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }

    // Relasi ke dosen pembimbing dari mahasiswa yang dikunjungi
    public function dosenPembimbing()
    {
        return $this->belongsTo(DosenPembimbing::class, 'id_dosen_pembimbing', 'id_dosen_pembimbing');
    }
}