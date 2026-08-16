<?php

// File model ini dipakai untuk menyimpan data token saat user lupa password atau ingin reset password

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    // Menentukan nama tabel
    protected $table = 'reset_password';
    // Menentukan primary key
    protected $primaryKey = 'id_reset_password';
    // Primary key bertambah otomatis
    public $incrementing = true;
    // Mematikan fitur waktu otomatis
    public $timestamps = false;

    // Kolom yang bisa diisi
    protected $fillable = [
        'id_user',
        'token',
        'expired_at',
        'is_used',
        'created_at',
    ];

    // Mengatur format tipe data saat data diambil dari database
    protected $casts = [
        'is_used' => 'boolean',
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Fungsi relasi ke tabel User
    // Artinya data reset password ini dimiliki oleh satu user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}