<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Controller ini ngurus proses login dan logout
 * 
 * Alurnya simple:
 * 1. User buka halaman login
 * 2. User masukin username & password
 * 3. Kalau cocok -> cek apakah wajib ganti password
 *    - Kalau ya -> arahkan ke halaman ganti password
 *    - Kalau tidak -> arahkan ke dashboard sesuai role
 * 4. Kalau gak cocok -> kembali ke halaman login dengan pesan error
 */
class LoginController extends Controller
{
    /**
     * Menampilkan halaman form login
     */
    public function index()
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi (mengecek username & password)
     */
    public function store(Request $request)
    {
        // Validasi: username dan password wajib diisi
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Cocokkan username & password dengan data di tabel users
        if (Auth::attempt($credentials)) {
            // Kalau berhasil login, regenerate session biar aman dari session fixation attack
            $request->session()->regenerate();

            $user = Auth::user();

            // CEK: Apakah user ini wajib ganti password?
            // Berlaku untuk SEMUA role (termasuk admin)
            // Biasanya saat pertama kali login atau setelah reset password oleh admin
            if ($user->must_change_password) {
                return redirect()->route('password.change.form');
            }

            // Arahkan ke dashboard masing-masing berdasarkan role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'mahasiswa':
                    return redirect()->route('mahasiswa.pengajuan_magang.index');
                case 'dosen':
                    return redirect()->route('dosen.pilih-role');
                case 'pegawai':
                    return redirect()->route('pegawai.pilih-role');
                default:
                    // Kalau role-nya gak dikenali, arahkan ke beranda pengunjung
                    return redirect()->route('beranda_pengunjung');
            }
        }

        // Kalau login gagal, kembali ke halaman login dengan pesan error
        // withInput() biar username yang sudah diketik tetap ada di field
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput();
    }

    /**
     * Proses logout
     * 
     * Yang dilakukan:
     * 1. Logout user
     * 2. Invalidate session (hapus semua data session)
     * 3. Regenerate token CSRF (biar token lama gak bisa dipakai lagi)
     * 4. Redirect ke halaman utama
     */
    public function destroy(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}