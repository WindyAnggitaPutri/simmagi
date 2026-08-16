<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalSemgang extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'jadwal_semgang';
    
    // Primary key-nya
    protected $primaryKey = 'id_jadwal_semgang';

    // Kolom yang boleh diisi massal, isinya data jadwal seminar magang
    // Mulai dari id mahasiswa, dosen pembimbing, judul, waktu, ruang, sampe rincian nilai-nilainya
    protected $fillable = [
        'id_mahasiswa',
        'id_dosen_pembimbing',
        'id_perusahaan',
        'judul_laporan',
        'tgl_semgang',
        'jam_mulai',
        'jam_selesai',
        'ruang',
        'status_semgang',
        'nilai_persiapan',
        'nilai_sikap',
        'nilai_penguasaan',
        'nilai_mempertahankan_ide',
        'nilai_hasil_magang',
        'nilai_penulisan',
        'total_nilai',
        'rata_rata',
        'catatan',
    ];

    // Relasi (sesuaikan dengan nama model di projectmu)
    // Relasi ke mahasiswa yang jadwal seminarnya diatur
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    // Relasi ke dosen pembimbing mahasiswa tersebut
    public function dosenPembimbing()
    {
        return $this->belongsTo(DosenPembimbing::class, 'id_dosen_pembimbing', 'id_dosen_pembimbing');
    }

    // Relasi ke perusahaan tempat mahasiswa tersebut magang
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }
}