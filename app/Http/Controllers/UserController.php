<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // list semua user, bisa search
    public function index(Request $request)
    {
        $query = User::with(['mahasiswa', 'dosen', 'pegawai']);

        if ($request->has('search') && $request->search != '') {
            $query->where('username', 'like', '%' . $request->search . '%')
                  ->orWhere('role', 'like', '%' . $request->search . '%');
        }

        $users = $query->orderBy('id_user', 'asc')->get();

        return view('pages.admin.user.index', compact('users'));
    }

    // form tambah user baru
    public function create()
    {
        $roles = ['admin', 'dosen', 'mahasiswa', 'pegawai'];
        return view('pages.admin.user.create', compact('roles'));
    }

    // simpan user baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6|max:100',
            'role'     => 'required|in:admin,dosen,mahasiswa,pegawai',
        ]);

        User::create([
            'username'             => $validated['username'],
            'password'             => Hash::make($validated['password']),
            'role'                 => $validated['role'],
            'must_change_password' => 1, // ✅ OTOMATIS WAJIB GANTI PASSWORD
        ]);

        return redirect()->route('admin.user.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    // form edit user
    public function edit($id_user)
    {
        $user = User::findOrFail($id_user);
        $roles = ['admin', 'dosen', 'mahasiswa', 'pegawai'];
        return view('pages.admin.user.edit', compact('user', 'roles'));
    }

    // update data user
    public function update(Request $request, $id_user)
    {
        $user = User::findOrFail($id_user);

        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users,username,' . $id_user . ',id_user',
            'role'     => 'required|in:admin,dosen,mahasiswa,pegawai',
            'password' => 'nullable|string|min:6|max:100', // password gak wajib diisi klo gak mau diganti
        ]);

        $data = [
            'username' => $validated['username'],
            'role'     => $validated['role'],
            // ✅ must_change_password TIDAK DIUBAH, biar tetap sesuai status aslinya
        ];

        // klo password diisi baru diupdate
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.user.index')
            ->with('success', 'Data user berhasil diperbarui');
    }

    // hapus user
    public function destroy($id_user)
    {
        $user = User::findOrFail($id_user);

        // cek dulu user ini terhubung ke data siapa, buat info di pesan success
        $terhubung = [];

        if ($user->role === 'mahasiswa') {
            $mhs = DB::table('mahasiswa')->where('id_user', $id_user)->first();
            if ($mhs) $terhubung[] = "Data Mahasiswa (NPM: {$mhs->npm})";
        }

        if ($user->role === 'dosen') {
            $dsn = DB::table('dosen')->where('id_user', $id_user)->first();
            if ($dsn) $terhubung[] = "Data Dosen (NIDN: {$dsn->nidn})";
        }

        if ($user->role === 'pegawai') {
            $pgw = DB::table('pegawai')->where('id_user', $id_user)->first();
            if ($pgw) $terhubung[] = "Data Pegawai (NIP: {$pgw->nip})";
        }

        $user->delete();

        // pesan nya dikasih info data mana aja yang ikut kehapus
        $pesan = 'User berhasil dihapus';
        if (!empty($terhubung)) {
            $pesan .= ' (terhubung ke: ' . implode(', ', $terhubung) . ')';
        }

        return redirect()->route('admin.user.index')
            ->with('success', $pesan);
    }
}