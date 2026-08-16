<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jabatan;

class JabatanController extends Controller
{
    /**
     * Menampilkan halaman daftar semua jabatan
     */
    public function index(Request $request)
    {
        $query = Jabatan::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_jabatan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_jabatan', 'like', '%' . $request->search . '%');
        }

        $jabatan = $query->orderBy('id_jabatan', 'asc')->get();

        return view('pages.admin.jabatan.index', compact('jabatan'));
    }

    /**
     * Menampilkan form untuk menambah jabatan baru
     * Auto-generate kode jabatan
     */
    public function create()
    {
        // Ambil kode jabatan terakhir, urutkan descending
        $lastJabatan = Jabatan::orderBy('kode_jabatan', 'desc')->first();
        
        if ($lastJabatan) {
            // Ambil angka dari kode terakhir (J004 -> 4)
            $lastNumber = (int) substr($lastJabatan->kode_jabatan, 1);
            $nextNumber = $lastNumber + 1;
        } else {
            // Kalau belum ada data sama sekali
            $nextNumber = 1;
        }
        
        // Format kode baru: J + 3 digit (J001, J002, J005, dst)
        $nextKodeJabatan = 'J' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        return view('pages.admin.jabatan.create', compact('nextKodeJabatan'));
    }

    /**
     * Menyimpan jabatan baru ke database
     * Kode jabatan di-generate otomatis di sini (bukan dari form)
     */
    public function store(Request $request)
    {
        // Validasi HANYA nama jabatan (kode jabatan tidak perlu divalidasi)
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:100',
        ]);

        // Generate kode jabatan otomatis
        $lastJabatan = Jabatan::orderBy('kode_jabatan', 'desc')->first();
        
        if ($lastJabatan) {
            $lastNumber = (int) substr($lastJabatan->kode_jabatan, 1);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        $kodeJabatan = 'J' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Simpan ke database
        Jabatan::create([
            'kode_jabatan' => $kodeJabatan,
            'nama_jabatan' => $validated['nama_jabatan'],
        ]);

        return redirect()->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan');
    }

    /**
     * Menampilkan form edit jabatan
     */
    public function edit($id_jabatan)
    {
        $jabatan = Jabatan::findOrFail($id_jabatan);
        return view('pages.admin.jabatan.edit', compact('jabatan'));
    }

    /**
     * Mengupdate data jabatan di database
     * HANYA nama jabatan yang bisa diubah
     */
    public function update(Request $request, $id_jabatan)
    {
        $jabatan = Jabatan::findOrFail($id_jabatan);

        // Validasi HANYA nama jabatan
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:100',
        ]);

        // Update HANYA nama jabatan (kode jabatan tidak berubah)
        $jabatan->update([
            'nama_jabatan' => $validated['nama_jabatan'],
        ]);

        return redirect()->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil diperbarui');
    }

    /**
     * Menghapus data jabatan dari database
     */
    public function destroy($id_jabatan)
    {
        $jabatan = Jabatan::findOrFail($id_jabatan);
        $jabatan->delete();

        return redirect()->route('admin.jabatan.index')
            ->with('success', 'Jabatan berhasil dihapus');
    }
}