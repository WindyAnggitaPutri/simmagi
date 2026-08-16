<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controller ini ngurus proses ganti password
 * 
 * Biasanya dipanggil saat:
 * - Pertama kali login (karena akun baru punya flag must_change_password = 1)
 * - Atau saat user ingin mengubah password dari menu pengaturan
 * 
 * Alurnya:
 * 1. User buka form ganti password
 * 2. User masukkan password baru + konfirmasi
 * 3. Sistem update password di database
 * 4. User OTOMATIS di-logout dan harus login ulang dengan password baru
 * 
 * Kenapa harus logout? Biar session lama gak bisa dipakai lagi,
 * jadi lebih aman secara keamanan.
 */
class PasswordController extends Controller
{
    /**
     * Menampilkan halaman form ganti password
     */
    public function edit()
    {
        return view('auth.change-password');
    }

    /**
     * Proses menyimpan password baru ke database
     */
    public function update(Request $request)
    {
        // Validasi input:
        // - password: wajib diisi, minimal 6 karakter, dan harus cocok dengan field konfirmasi
        // - Pesan error kustom dalam Bahasa Indonesia
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $user = auth()->user();

        // Update 2 hal sekaligus:
        // 1. Password di-hash dulu sebelum disimpan (standar keamanan Laravel)
        // 2. Flag must_change_password di-set jadi false, artinya user sudah ganti password
        //    jadi kalau dia login lagi nanti, gak akan dilempar ke halaman ganti password lagi
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        // LOGOUT USER SETELAH GANTI PASSWORD
        // Ini langkah keamanan: session lama dianggap tidak valid lagi
        // User harus login ulang dengan password barunya
        auth()->logout();

        // Invalidasi session lama (hapus semua data session)
        // dan regenerasi CSRF token (biar token lama gak bisa dipakai untuk serangan)
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('success', 'Kata sandi berhasil diubah! Silakan login kembali menggunakan kata sandi baru Anda.');
    }
}