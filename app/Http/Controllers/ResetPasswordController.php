<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Pegawai;
use App\Models\ResetPassword;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    // ==========================================
    // TAMPILKAN FORM INPUT EMAIL
    // ==========================================
    public function showForgotForm()
    {
        return view('auth.forgot_password');
    }

    // ==========================================
    // PROSES KIRIM LINK RESET KE EMAIL
    // ==========================================
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $email = $request->email;

        $user = null;
        $nama = null;

        // cari email nya di 3 tabel, karena user bisa dari mana aja
        // Cek di tabel mahasiswa
        $mahasiswa = Mahasiswa::where('email', $email)->first();
        if ($mahasiswa) {
            $user = User::find($mahasiswa->id_user);
            $nama = $mahasiswa->nama;
        }

        // Cek di tabel dosen
        if (!$user) {
            $dosen = Dosen::where('email', $email)->first();
            if ($dosen) {
                $user = User::find($dosen->id_user);
                $nama = $dosen->nama;
            }
        }

        // Cek di tabel pegawai
        if (!$user) {
            $pegawai = Pegawai::where('email', $email)->first();
            if ($pegawai) {
                $user = User::find($pegawai->id_user);
                $nama = $pegawai->nama;
            }
        }

        // Jika email tidak ditemukan
        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak ditemukan dalam sistem.',
            ])->withInput();
        }

        // bersihin token lama yang belum dipake, biar gak numpuk
        ResetPassword::where('id_user', $user->id_user)
            ->where('is_used', false)
            ->delete();

        // bikin token baru, valid 60 menit
        $token = Str::random(64);
        $expiredAt = Carbon::now()->addMinutes(60);

        ResetPassword::create([
            'id_user' => $user->id_user,
            'token' => $token,
            'expired_at' => $expiredAt,
            'is_used' => false,
            'created_at' => Carbon::now(),
        ]);

        // Buat URL reset
        $resetUrl = url('/reset-password/' . $token);

        // Kirim email
        Mail::to($email)->send(new ResetPasswordMail($resetUrl, $nama, $expiredAt));

       

        return back()->with('success', 'Link reset password telah dikirim ke email Anda. Silakan cek email (juga cek folder spam). Link berlaku 60 menit.');
    }

    // ==========================================
    // TAMPILKAN FORM GANTI PASSWORD (via token)
    // ==========================================
    public function showResetForm($token)
    {
        $reset = ResetPassword::where('token', $token)->first();

        // cek token nya ada gak
        if (!$reset) {
            return redirect()->route('forgot.password')
                ->withErrors(['token' => 'Link reset password tidak valid.']);
        }

        // cek udah pernah dipake belum
        if ($reset->is_used) {
            return redirect()->route('forgot.password')
                ->withErrors(['token' => 'Link reset password sudah pernah digunakan. Silakan ajukan ulang.']);
        }

        // cek expired belum
        if (Carbon::now()->isAfter($reset->expired_at)) {
            return redirect()->route('forgot.password')
                ->withErrors(['token' => 'Link reset password sudah kedaluwarsa. Silakan ajukan ulang.']);
        }

        return view('auth.reset_password', compact('token'));
    }

    // ==========================================
    // PROSES SIMPAN PASSWORD BARU
    // ==========================================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $reset = ResetPassword::where('token', $request->token)->first();

        // validasi ulang token nya di backend jga, takut url nya diubah manual
        if (!$reset) {
            return redirect()->route('forgot.password')
                ->withErrors(['token' => 'Link reset password tidak valid.']);
        }

        if ($reset->is_used) {
            return redirect()->route('forgot.password')
                ->withErrors(['token' => 'Link reset password sudah pernah digunakan.']);
        }

        if (Carbon::now()->isAfter($reset->expired_at)) {
            return redirect()->route('forgot.password')
                ->withErrors(['token' => 'Link reset password sudah kedaluwarsa.']);
        }

        // Update password user
        $user = User::find($reset->id_user);
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'must_change_password' => false,
        ]);

        // tandai token nya udah dipake
        $reset->update([
            'is_used' => true,
        ]);

        return redirect()->route('login')->with('success', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }
}