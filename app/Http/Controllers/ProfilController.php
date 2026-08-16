<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    // tampilan form edit profil, beda beda tiap role
    public function edit()
    {
        $user = Auth::user();
        $profil = null;
        $role = $user->role;
        $berandaUrl = '/'; // Default fallback

        if ($role == 'mahasiswa') {
            $profil = Mahasiswa::where('id_user', $user->id_user)->first();
            $berandaUrl = '/beranda-mahasiswa';
        } elseif ($role == 'dosen') {
            $profil = Dosen::where('id_user', $user->id_user)->first();
            
            // dosen punya banyak sub-role, jadi cek dari session
            $activeRole = session('dosen_active_role');
            if ($activeRole == 'kajur') $berandaUrl = '/beranda-kajur';
            elseif ($activeRole == 'kaprodi') $berandaUrl = '/beranda-kaprodi';
            elseif ($activeRole == 'dospem') $berandaUrl = '/beranda-dospem';
            elseif ($activeRole == 'dosen_visitasi') $berandaUrl = '/beranda-dosen-visitasi';
            else $berandaUrl = '/beranda-dosen';
            
        } elseif ($role == 'pegawai') {
            $profil = Pegawai::where('id_user', $user->id_user)->first();
            
            // pegawai juga punya sub-role
            $activeRole = session('role_pegawai');
            if ($activeRole == 'baak') $berandaUrl = '/beranda-baak';
            elseif ($activeRole == 'baup') $berandaUrl = '/beranda-baup';
            else $berandaUrl = '/beranda-pegawai';
            
        } elseif ($role == 'admin') {
            $berandaUrl = '/admin/dashboard';
        }

        return view('pages.edit_profil', compact('profil', 'role', 'berandaUrl'));
    }

    // simpan perubahan profil
    public function update(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;

        // rule dasar yang berlaku buat semua role
        $validationRules = [
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'ttd_digital' => 'nullable|file|max:2048',
        ];

        // email harus unique per tabel masing2, jadi rule nya dibedain
        if ($role == 'mahasiswa') {
    $profil = Mahasiswa::where('id_user', $user->id_user)->firstOrFail();
            $validationRules['email'] = 'required|email|max:100|unique:mahasiswa,email,' . $profil->id_mahasiswa . ',id_mahasiswa';
        } elseif ($role == 'dosen') {
            $profil = Dosen::where('id_user', $user->id_user)->firstOrFail();
            $validationRules['email'] = 'required|email|max:100|unique:dosen,email,' . $profil->id_dosen . ',id_dosen';
        } elseif ($role == 'pegawai') {
            $profil = Pegawai::where('id_user', $user->id_user)->firstOrFail();
            $validationRules['email'] = 'required|email|max:100|unique:pegawai,email,' . $profil->id_pegawai . ',id_pegawai';
        } else {
            abort(403);
        }

        $validated = $request->validate($validationRules);

        // klo upload ttd baru, hapus yang lama dulu
        if ($request->hasFile('ttd_digital')) {
            if ($profil->ttd_digital && file_exists(storage_path('app/public/ttd_digital/' . $profil->ttd_digital))) {
                unlink(storage_path('app/public/ttd_digital/' . $profil->ttd_digital));
            }

            $file = $request->file('ttd_digital');
            // $namaTTD = time() . '_' . $file->getClientOriginalName();
             $namaTTD = $file->getClientOriginalName();
            $file->storeAs('ttd_digital', $namaTTD, 'public');
            $validated['ttd_digital'] = $namaTTD;
        }

        $profil->update($validated);

        return redirect()->route('profil.edit')->with('success', 'Profil berhasil diperbarui!');
    }

    // ganti password sendiri
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();
        
        $user->password = Hash::make($request->password);
        
        // klo ini password pertama kali yang diganti, reset flag nya
        if ($user->must_change_password == 1) {
            $user->must_change_password = 0;
        }
        
        $user->save();

        return redirect()->route('profil.edit')->with('success', 'Password berhasil diperbarui!');
    }
}