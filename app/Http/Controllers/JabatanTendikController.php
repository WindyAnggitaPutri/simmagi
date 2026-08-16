<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JabatanTendik;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Prodi; // Tambahan untuk cek jurusan

/**
 * Controller untuk mengelola penugasan Jabatan ke Dosen atau Pegawai (Tendik)
 * 
 * Fitur utama:
 * - Menugaskan jabatan (misal: Kaprodi, Kajur, Sekretaris) ke dosen atau pegawai
 * - Validasi khusus: Kaprodi hanya boleh 1 per Prodi, Kajur hanya boleh 1 per Jurusan
 * - Menyimpan TTD digital ke tabel aslinya (dosen/pegawai)
 */
class JabatanTendikController extends Controller
{
    /**
     * Menampilkan daftar semua penugasan jabatan
     * 
     * Bisa cari berdasarkan:
     * - Nama dosen
     * - Nama pegawai
     * - Nama jabatan
     */
    public function index(Request $request)
    {
        // Query dengan relasi supaya tidak N+1 query
        $query = JabatanTendik::with(['dosen', 'pegawai', 'jabatan']);

        // Kalau ada kata kunci pencarian...
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('dosen', function($q) use ($request) {
                      // Cari di nama dosen
                      $q->where('nama', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('pegawai', function($q) use ($request) {
                      // Atau cari di nama pegawai
                      $q->where('nama', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('jabatan', function($q) use ($request) {
                      // Atau cari di nama jabatan
                      $q->where('nama_jabatan', 'like', '%' . $request->search . '%');
                  });
        }

        // Ambil semua data, urutkan dari ID terkecil
        $jabatanTendik = $query->orderBy('id_jabatan_tendik', 'asc')->get();

        return view('pages.admin.jabatan_tendik.index', compact('jabatanTendik'));
    }

    /**
     * Menampilkan form untuk menugaskan jabatan baru
     * 
     * Dropdown "Orang" berisi gabungan data Dosen dan Pegawai
     * Format value-nya: "dosen-ID" atau "pegawai-ID" biar bisa dibedakan saat simpan
     */
    public function create()
    {
        // Ambil data dosen, format ID-nya jadi "id" supaya seragam dengan pegawai
        $dosen = Dosen::select('id_dosen as id', 'nama')
            ->get()
            ->map(function($item) {
                // Tambahkan flag type = "dosen" biar tahu asalnya dari mana
                return (object)['id' => $item->id, 'nama' => $item->nama, 'type' => 'dosen'];
            });

        // Ambil data pegawai, format ID-nya jadi "id" supaya seragam
        $pegawai = Pegawai::select('id_pegawai as id', 'nama')
            ->get()
            ->map(function($item) {
                // Tambahkan flag type = "pegawai"
                return (object)['id' => $item->id, 'nama' => $item->nama, 'type' => 'pegawai'];
            });

        // Gabungkan dua collection jadi satu untuk dropdown
        $orang = $dosen->merge($pegawai);

        // Ambil daftar jabatan untuk dropdown
        $jabatan = Jabatan::orderBy('nama_jabatan', 'asc')->get();
                
        return view('pages.admin.jabatan_tendik.create', compact('orang', 'jabatan'));
    }

    /**
     * Menyimpan penugasan jabatan baru
     * 
     * Alur:
     * 1. Validasi input standar
     * 2. Pecah value dropdown "orang" jadi type (dosen/pegawai) dan ID-nya
     * 3. VALIDASI KHUSUS: Cek kalau jabatannya Kaprodi/Kajur, pastikan belum ada yang menjabat di unit tersebut
     * 4. Simpan data jabatan tendik
     * 5. Simpan TTD digital ke tabel aslinya (dosen atau pegawai)
     */
    public function store(Request $request)
    {
        // Validasi input dasar
        $validated = $request->validate([
            'orang'        => 'required', // Format: "dosen-1" atau "pegawai-2"
            'id_jabatan'   => 'required|exists:jabatan,id_jabatan',
            'ttd_digital'  => 'nullable|string|max:100',
        ]);

        // Pecah value dropdown, misal "dosen-5" jadi ["dosen", "5"]
        [$type, $id] = explode('-', $request->orang);
        $id_jabatan = $request->id_jabatan;
        
        // Ambil objek jabatan untuk cek nama jabatannya apa
        $jabatanObj = Jabatan::find($id_jabatan);

        // ==========================================
        // ✅ VALIDASI KAPRODI & KAJUR (HANYA 1 PER UNIT)
        // ==========================================
        if ($jabatanObj) {
            // Ubah ke huruf kecil semua biar mudah dicocokkan
            $namaJabatanLower = strtolower($jabatanObj->nama_jabatan);

            // CEK KAPRODI (1 Per Prodi) - Kalau jabatannya Ketua Program Studi
            if ($namaJabatanLower == 'ketua program studi') {
                // Hanya berlaku kalau yang dipilih adalah dosen
                if ($type == 'dosen') {
                    $dosenTarget = Dosen::find($id);
                    if ($dosenTarget && $dosenTarget->id_prodi) {
                        // Cek apakah di prodi dosen ini sudah ada Kaprodi-nya
                        $sudahAda = JabatanTendik::where('id_jabatan', $id_jabatan)
                            ->whereHas('dosen', function($q) use ($dosenTarget) {
                                $q->where('id_prodi', $dosenTarget->id_prodi);
                            })
                            ->exists();

                        // Kalau sudah ada, batalkan dan kasih pesan error
                        if ($sudahAda) {
                            return back()->withErrors(['orang' => 'Prodi dari dosen ini sudah memiliki Ketua Program Studi.'])->withInput();
                        }
                    }
                }
            }

            // CEK KAJUR (1 Per Jurusan) - Kalau jabatannya Ketua Jurusan
            if ($namaJabatanLower == 'ketua jurusan') {
                // Hanya berlaku kalau yang dipilih adalah dosen
                if ($type == 'dosen') {
                    // Load relasi prodi untuk cek jurusan-nya
                    $dosenTarget = Dosen::with('prodi')->find($id);
                    if ($dosenTarget && $dosenTarget->prodi && $dosenTarget->prodi->id_jurusan) {
                        // Ambil ID jurusan dari prodi dosen ini
                        $idJurusanTarget = $dosenTarget->prodi->id_jurusan;

                        // Cek apakah di jurusan ini sudah ada Kajur-nya
                        $sudahAda = JabatanTendik::where('id_jabatan', $id_jabatan)
                            ->whereHas('dosen.prodi', function($q) use ($idJurusanTarget) {
                                $q->where('id_jurusan', $idJurusanTarget);
                            })
                            ->exists();

                        // Kalau sudah ada, batalkan dan kasih pesan error
                        if ($sudahAda) {
                            return back()->withErrors(['orang' => 'Jurusan dari dosen ini sudah memiliki Ketua Jurusan.'])->withInput();
                        }
                    }
                }
            }
        }
        // ==========================================

        // Siapkan data yang akan disimpan
        $data = [
            'id_jabatan'  => $request->id_jabatan,
        ];

        // Set ID dosen atau pegawai berdasarkan type-nya, yang satunya diisi null
        if ($type == 'dosen') {
            $data['id_dosen'] = $id;
            $data['id_pegawai'] = null;
        } else {
            $data['id_dosen'] = null;
            $data['id_pegawai'] = $id;
        }

        // Simpan data penugasan jabatan
        JabatanTendik::create($data);

        // Kalau ada input TTD digital, simpan ke tabel aslinya (bukan ke tabel jabatan_tendik)
        if ($request->filled('ttd_digital')) {
            if ($type == 'dosen') {
                // Update TTD di tabel dosen
                Dosen::where('id_dosen', $id)->update(['ttd_digital' => $request->ttd_digital]);
            } else {
                // Update TTD di tabel pegawai
                Pegawai::where('id_pegawai', $id)->update(['ttd_digital' => $request->ttd_digital]);
            }
        }

        return redirect()->route('admin.jabatan-dosen.index')
            ->with('success', 'Jabatan berhasil ditambahkan');
    }

    /**
     * Menampilkan form edit penugasan jabatan
     * 
     * Sama seperti create, tapi data yang sedang diedit sudah terisi di form
     * 
     * @param int $id_jabatan_tendik - ID penugasan yang akan diedit
     */
    public function edit($id_jabatan_tendik)
    {
        // Ambil data jabatan tendik yang sedang diedit
        $jabatanTendik = JabatanTendik::findOrFail($id_jabatan_tendik);

        // Ambil daftar jabatan untuk dropdown
        $jabatan = Jabatan::orderBy('nama_jabatan', 'asc')->get();

        // Siapkan dropdown orang (sama persis seperti di create)
        $dosen = Dosen::select('id_dosen as id', 'nama')
            ->get()
            ->map(function($item) {
                return (object)['id' => $item->id, 'nama' => $item->nama, 'type' => 'dosen'];
            });

        $pegawai = Pegawai::select('id_pegawai as id', 'nama')
            ->get()
            ->map(function($item) {
                return (object)['id' => $item->id, 'nama' => $item->nama, 'type' => 'pegawai'];
            });

        $orang = $dosen->merge($pegawai);

        return view('pages.admin.jabatan_tendik.edit', compact('jabatanTendik', 'orang', 'jabatan'));
    }

    /**
     * Mengupdate data penugasan jabatan
     * 
     * Alurnya sama persis dengan store, tapi ada 1 perbedaan penting:
     * Saat validasi Kaprodi/Kajur, data yang sedang diedit DIKECUALIKAN
     * (biar nggak ngecek dirinya sendiri dan menganggap "sudah ada")
     * 
     * @param int $id_jabatan_tendik - ID penugasan yang akan diupdate
     */
    public function update(Request $request, $id_jabatan_tendik)
    {
        // Ambil data lama
        $jabatanTendik = JabatanTendik::findOrFail($id_jabatan_tendik);

        // Validasi input dasar
        $validated = $request->validate([
            'orang'        => 'required',
            'id_jabatan'   => 'required|exists:jabatan,id_jabatan',
            'ttd_digital'  => 'nullable|string|max:100',
        ]);

        // Pecah value dropdown
        [$type, $id] = explode('-', $request->orang);
        $id_jabatan = $request->id_jabatan;
        $jabatanObj = Jabatan::find($id_jabatan);

        // ==========================================
        // ✅ VALIDASI KAPRODI & KAJUR (HANYA 1 PER UNIT)
        // ==========================================
        if ($jabatanObj) {
            $namaJabatanLower = strtolower($jabatanObj->nama_jabatan);

            // CEK KAPRODI (1 Per Prodi)
            if ($namaJabatanLower == 'ketua program studi') {
                if ($type == 'dosen') {
                    $dosenTarget = Dosen::find($id);
                    if ($dosenTarget && $dosenTarget->id_prodi) {
                        $sudahAda = JabatanTendik::where('id_jabatan', $id_jabatan)
                            // ⬇️ PENTING: Kecualikan dirinya sendiri saat edit
                            ->where('id_jabatan_tendik', '!=', $id_jabatan_tendik) // Kecuali dirinya sendiri saat edit
                            ->whereHas('dosen', function($q) use ($dosenTarget) {
                                $q->where('id_prodi', $dosenTarget->id_prodi);
                            })
                            ->exists();

                        if ($sudahAda) {
                            return back()->withErrors(['orang' => 'Prodi dari dosen ini sudah memiliki Ketua Program Studi.'])->withInput();
                        }
                    }
                }
            }

            // CEK KAJUR (1 Per Jurusan)
            if ($namaJabatanLower == 'ketua jurusan') {
                if ($type == 'dosen') {
                    $dosenTarget = Dosen::with('prodi')->find($id);
                    if ($dosenTarget && $dosenTarget->prodi && $dosenTarget->prodi->id_jurusan) {
                        $idJurusanTarget = $dosenTarget->prodi->id_jurusan;

                        $sudahAda = JabatanTendik::where('id_jabatan', $id_jabatan)
                            // ⬇️ PENTING: Kecualikan dirinya sendiri saat edit
                            ->where('id_jabatan_tendik', '!=', $id_jabatan_tendik) // Kecuali dirinya sendiri saat edit
                            ->whereHas('dosen.prodi', function($q) use ($idJurusanTarget) {
                                $q->where('id_jurusan', $idJurusanTarget);
                            })
                            ->exists();

                        if ($sudahAda) {
                            return back()->withErrors(['orang' => 'Jurusan dari dosen ini sudah memiliki Ketua Jurusan.'])->withInput();
                        }
                    }
                }
            }
        }
        // ==========================================

        // Siapkan data update
        $data = [
            'id_jabatan'  => $request->id_jabatan,
        ];

        // Set ID dosen atau pegawai
        if ($type == 'dosen') {
            $data['id_dosen'] = $id;
            $data['id_pegawai'] = null;
        } else {
            $data['id_dosen'] = null;
            $data['id_pegawai'] = $id;
        }

        // Update data di database
        $jabatanTendik->update($data);

        // Update TTD digital ke tabel aslinya kalau ada isinya
        if ($request->filled('ttd_digital')) {
            if ($type == 'dosen') {
                Dosen::where('id_dosen', $id)->update(['ttd_digital' => $request->ttd_digital]);
            } else {
                Pegawai::where('id_pegawai', $id)->update(['ttd_digital' => $request->ttd_digital]);
            }
        }

        return redirect()->route('admin.jabatan-dosen.index')
            ->with('success', 'Jabatan Dosen/Pegawai berhasil diperbarui');
    }

    /**
     * Menghapus penugasan jabatan
     * 
     * @param int $id_jabatan_tendik - ID penugasan yang akan dihapus
     */
    public function destroy($id_jabatan_tendik)
    {
        // Cari dan hapus data
        $jabatanTendik = JabatanTendik::findOrFail($id_jabatan_tendik);
        $jabatanTendik->delete();

        return redirect()->route('admin.jabatan-dosen.index')
            ->with('success', 'Jabatan berhasil dihapus');
    }
}