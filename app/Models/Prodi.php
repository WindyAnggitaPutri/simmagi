<?php

// File model ini digunakan untuk mengatur data program studi di kampus

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;

    // Menentukan nama tabel
    protected $table = 'prodi';
    // Menentukan primary key
    protected $primaryKey = 'id_prodi';
    // Primary key bertambah otomatis
    public $incrementing = true;
    // Tipe data primary key integer
    protected $keyType = 'int';

    // Mematikan fitur waktu otomatis
    public $timestamps = false;

    // Daftar kolom yang bisa diisi
    protected $fillable = [
        'id_jurusan',
        'kode_prodi',
        'nama_prodi',
        'jenjang',
        'semester_magang',
        'semester_pengajuan_magang',
    ];

    // Fungsi ini untuk membuat relasi ke tabel jurusan
    // Artinya satu prodi ini dimiliki oleh satu jurusan
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id_jurusan');
    }
}