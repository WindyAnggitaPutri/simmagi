<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'pegawai';
    
    // Primary key-nya
    protected $primaryKey = 'id_pegawai';
    
    // Id-nya auto-increment dan tipenya integer
    public $incrementing = true;
    protected $keyType = 'int';
    
    // Timestamps dimatiin
    public $timestamps = false;

    // Daftar kolom yang boleh diisi massal, isinya data dasar pegawai
    protected $fillable = [
        'id_user',
        'nip',
        'nama',
        'no_hp',
        'email',
        
        'ttd_digital',
        'tgl_lahir',
        'jenis_kelamin',
    ];

    // Ngubah tipe data tgl_lahir jadi format tanggal
    protected $casts = [
        'tgl_lahir' => 'date',
    ];

  
    // Relasi ke tabel jabatan tendik, artinya satu pegawai bisa punya banyak jabatan
public function jabatanTendik()
{
    return $this->hasMany(JabatanTendik::class, 'id_pegawai');
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