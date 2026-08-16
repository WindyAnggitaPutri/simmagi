<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'mahasiswa';
    
    // Primary key-nya pakai id_mahasiswa
    protected $primaryKey = 'id_mahasiswa';
    
    // Id-nya auto-increment dan tipenya integer
    public $incrementing = true;
    protected $keyType = 'int';
    
    // Timestamps dimatiin soalnya nggak pakai kolom waktu bawaan Laravel
    public $timestamps = false;

    // Daftar kolom yang boleh diisi massal, isinya data dasar mahasiswa
    protected $fillable = [
        'id_user',       
        'npm',           
        'nama',
        'tgl_lahir',
        'jenis_kelamin',
        'id_prodi',
        'angkatan',
        'semester',
        'kelas',
        'no_hp',
        'email',
        'ttd_digital',
    ];

    // Ngubah format data pas diambil dari database
    protected $casts = [
        'tgl_lahir' => 'date', // diubah jadi format tanggal
        'angkatan' => 'integer', // diubah jadi angka bulat
    ];

       // ============================================
    // MUTATOR: Otomatis format nama jadi Title Case
    // Contoh: "windy anggita putri" → "Windy Anggita Putri"
    // Berlaku untuk create, edit, DAN import
    // ============================================
    public function setNamaAttribute($value)
    {
        $this->attributes['nama'] = ucwords(mb_strtolower(trim($value)));
    }


    


    // ============================================
    // ACCESSOR: Kelas Lengkap (contoh: "3C", "2B")
    // Dihitung otomatis dari semester + huruf kelas
    // ============================================
// ============================================
// ACCESSOR: Kelas Lengkap (contoh: "3C", "2B", "D", "A")
// Dihitung otomatis dari semester + huruf kelas
// TI (D3/3tahun) mentok kelas 3, sisanya D4 (4tahun) mentok kelas 4
// Kalau sudah melebihi batas, tampilkan HURUF saja
// ============================================
public function getKelasLengkapAttribute()
{
    // Kalau data kelas atau semester kosong, balikin tanda strip
    if (!$this->kelas || !$this->semester) {
        return '-';
    }
    
    // Hitung tahun ajaran berdasarkan semester, misal semester 3 berarti tahun ke-2
    $tahunKelas = ceil($this->semester / 2);
    
    // Tentukan maks tahun kelas berdasarkan jenjang prodi
    $maxTahunKelas = 4; // Default D4 (RPL, RKS, MM, ALKS)
    if ($this->prodi) {
        $namaProdi = strtoupper($this->prodi->nama_prodi);
        // TI = D3, hanya sampai kelas 3
        if (strpos($namaProdi, 'TI') !== false) {
            $maxTahunKelas = 3;
        }
    }
    
    // Jika tahun kelas melebihi batas studi, tampilkan hanya huruf kelasnya saja
    if ($tahunKelas > $maxTahunKelas) {
        return $this->kelas;
    }
    
    // Gabungin angka tahun kelas sama huruf kelasnya, misal "3" + "C" jadi "3C"
    return $tahunKelas . $this->kelas;
}
    // Relasi ke tabel prodi, artinya mahasiswa ini milik satu prodi
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    
    
}