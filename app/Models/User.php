<?php

// File ini adalah model utama untuk sistem login atau autentikasi pengguna aplikasi

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Menentukan nama tabel
    protected $table = 'users';
    // Menentukan primary key
    protected $primaryKey = 'id_user'; 
    // Primary key bertambah otomatis
    public $incrementing = true;
    // Mematikan fitur waktu otomatis
    public $timestamps = false; 

    // Kolom yang bisa diisi saat membuat user baru
    protected $fillable = [
        'username',
        'password',
        'role',
        'must_change_password'
    ];

    // Kolom ini disembunyikan saat data user ditampilkan agar password tidak terlihat orang
    protected $hidden = [
        'password',
    ];

    // Mengatur kolom ini berupa nilai benar atau salah
    protected $casts = [
        'must_change_password' => 'boolean',
    ];

    // ================= RELASI =================
    // Relasi ke tabel Mahasiswa, satu user bisa punya satu data mahasiswa
    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class, 'id_user', 'id_user');
    }

    // Relasi ke tabel Dosen, satu user bisa punya satu data dosen
    public function dosen()
    {
        return $this->hasOne(Dosen::class, 'id_user', 'id_user');
    }

    // Relasi ke tabel Pegawai, satu user bisa punya satu data pegawai
    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, 'id_user', 'id_user');
    }

    // ================= ACCESSOR EMAIL =================
    // Fungsi ini untuk mencari email yang dipakai user berdasarkan perannya (role)
    public function getAssociatedEmailAttribute()
    {
        if ($this->role === 'mahasiswa' && $this->mahasiswa) return $this->mahasiswa->email;
        if ($this->role === 'dosen' && $this->dosen) return $this->dosen->email;
        if ($this->role === 'pegawai' && $this->pegawai) return $this->pegawai->email;
        return '-';
    }
}