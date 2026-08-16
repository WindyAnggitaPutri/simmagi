<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'dosen';
    
    // Primary key-nya pakai id_dosen, bukan id bawaan Laravel
    protected $primaryKey = 'id_dosen';
    
    // Ngasih tau kalau id-nya itu angka berurutan atau auto-increment
    public $incrementing = true;
    
    // Tipe data primary key-nya adalah integer
    protected $keyType = 'int';
    
    // Dimatiin soalnya di tabel ini nggak ada kolom created_at dan updated_at bawaan Laravel
    public $timestamps = false;

    // Kolom-kolom yang boleh diisi massal
    protected $fillable = [
        'id_user',
        'nidn',
        'nama',
        'tgl_lahir',
        'jenis_kelamin',
        'id_prodi',
        'no_hp',
        'email',
        'ttd_digital',   // TAMBAHKAN INI
    ];

    // Ngubah format tgl_lahir jadi tipe data date biar gampang diproses
    protected $casts = [
        'tgl_lahir' => 'date',
    ];

    // Relasi ke tabel prodi, artinya satu dosen cuman milik satu prodi
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }
    
    // Relasi ke tabel jabatan tendik, satu dosen bisa punya banyak jabatan tendik
    public function jabatanTendik()
    {
        return $this->hasMany(\App\Models\JabatanTendik::class, 'id_dosen', 'id_dosen');
    }
    
    // Relasi ke tabel dosen pembimbing, satu dosen bisa jadi pembimbing banyak mahasiswa
    public function dosenPembimbing()
    {
        return $this->hasMany(DosenPembimbing::class, 'id_dosen', 'id_dosen');
    }

         // ============================================
    // MUTATOR: Otomatis format nama jadi Title Case
    // Contoh: "windy anggita putri" → "Windy Anggita Putri"
    // Berlaku untuk create, edit, DAN import
    // ============================================

      public function setNamaAttribute($value)
    {
        $this->attributes['nama'] = ucwords(mb_strtolower(trim($value)));
    }
}