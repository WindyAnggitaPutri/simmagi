<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenPerusahaan extends Model
{
    protected $table = 'token_perusahaan';
    protected $primaryKey = 'id_token';
    public $timestamps = false;

    protected $fillable = [
        'id_perusahaan',
        'id_prodi',        // ✅ TAMBAHKAN INI
        'type',
        'id_mahasiswa',
        'token',
        'expired_at',
        'is_used',
        'accessed_at',
        'created_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'is_used' => 'boolean',
        'accessed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }

    // ✅ TAMBAHKAN: Relasi ke Prodi
    public function prodi()
    {
        return $this->belongsTo(\App\Models\Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function angket()
    {
        return $this->hasOne(AngketPerusahaan::class, 'id_token', 'id_token');
    }

    public function penilaian()
    {
        return $this->hasOne(PenilaianMahasiswa::class, 'id_token', 'id_token');
    }
}