<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use Illuminate\Validation\Rule;

class PerusahaanController extends Controller
{
    public function index(Request $request)
    {
        $query = Perusahaan::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_perusahaan', 'like', '%' . $request->search . '%')
                  ->orWhere('alamat', 'like', '%' . $request->search . '%');
        }

        $perusahaan = $query->orderBy('id_perusahaan', 'asc')->get();

        return view('pages.admin.perusahaan.index', compact('perusahaan'));
    }

    public function create()
    {
        return view('pages.admin.perusahaan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('perusahaan', 'nama_perusahaan'),
            ],
            'alamat' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'latitude.required' => 'Titik lokasi belum dipilih di peta. Silakan cari alamat lalu klik peta.',
            'longitude.required' => 'Titik lokasi belum dipilih di peta. Silakan cari alamat lalu klik peta.',
        ]);

        Perusahaan::create($validated);

        return redirect()->route('admin.perusahaan.index')
            ->with('success', 'Perusahaan berhasil ditambahkan');
    }

    public function edit($id_perusahaan)
    {
        $perusahaan = Perusahaan::findOrFail($id_perusahaan);
        return view('pages.admin.perusahaan.edit', compact('perusahaan'));
    }

    public function update(Request $request, $id_perusahaan)
    {
        $perusahaan = Perusahaan::findOrFail($id_perusahaan);

        $validated = $request->validate([
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('perusahaan', 'nama_perusahaan')->ignore($id_perusahaan, 'id_perusahaan'),
            ],
            'alamat' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'latitude.required' => 'Titik lokasi belum dipilih di peta. Silakan cari alamat lalu klik peta.',
            'longitude.required' => 'Titik lokasi belum dipilih di peta. Silakan cari alamat lalu klik peta.',
        ]);

        $perusahaan->update($validated);

        return redirect()->route('admin.perusahaan.index')
            ->with('success', 'Data perusahaan berhasil diperbarui');
    }

    public function destroy($id_perusahaan)
    {
        $perusahaan = Perusahaan::findOrFail($id_perusahaan);
        $perusahaan->delete();

        return redirect()->route('admin.perusahaan.index')
            ->with('success', 'Perusahaan berhasil dihapus');
    }
}