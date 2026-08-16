<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianAkhir extends Model
{
    // Nama tabel di database
    protected $table = 'penilaian_akhir';
    
    // Primary key-nya
    protected $primaryKey = 'id_nilai_akhir';
    
    // Dimatiin soalnya DB cuma punya created_at default, nggak ada updated_at bawaan Laravel
    public $timestamps = false; // DB cuma punya created_at default

    // Kolom yang boleh diisi massal, isinya data nilai akhir magang mahasiswa
    protected $fillable = [
        'id_mahasiswa',
        'id_dosen_pembimbing',
        'id_jadwal_semgang',
        'id_penilaian',
        'nilai_perusahaan',
        'nilai_pembimbing',
        'nilai_akhir',
        'grade',
    ];

    // Relasi ke mahasiswa yang nilainya dihitung
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    // Relasi ke dosen pembimbing yang ngasih nilai
    public function dosenPembimbing()
    {
        return $this->belongsTo(DosenPembimbing::class, 'id_dosen_pembimbing', 'id_dosen_pembimbing');
    }

    // Relasi ke jadwal seminar magangnya
    public function jadwalSemgang()
    {
        return $this->belongsTo(JadwalSemgang::class, 'id_jadwal_semgang', 'id_jadwal_semgang');
    }

    // Relasi ke data penilaian mahasiswa lainnya
    public function penilaianMahasiswa()
    {
        return $this->belongsTo(PenilaianMahasiswa::class, 'id_penilaian', 'id_penilaian');
    }
}