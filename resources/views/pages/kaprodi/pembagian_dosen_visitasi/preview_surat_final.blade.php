@extends('layouts.app')

@section('content')

{{-- ==================== TOMBOL AKSI ==================== --}}
<div class="no-print mx-auto py-4 flex justify-between items-center" style="width: 210mm; max-width: 100%;">
    
    {{-- Tombol Kembali --}}
    <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 border-2 border-yellow-500 text-yellow-600 font-bold text-sm px-5 py-2.5 hover:bg-yellow-500 hover:text-white transition-all duration-200 rounded-lg">
        ← {{ $backText }}
    </a>

    <div class="flex gap-3 items-center flex-wrap justify-end">
        
        {{-- ✅ TOMBOL KONFIRMASI (HANYA MUNCUL UNTUK KAJUR saat $bisaDiApprove = true) --}}
        <div id="btn-approve-group">
            @if($bisaDiApprove ?? false)
                <button onclick="openModalKonfirmasi()" class="bg-yellow-500 text-white text-sm px-4 py-2 hover:bg-yellow-600 transition rounded-lg font-semibold shadow-sm cursor-pointer">
                    ✅ Konfirmasi
                </button>
            @endif
        </div>

        {{-- ✅ BADGE SUDAH DISETUJUI (Muncul setelah Kajur konfirmasi) --}}
        <span id="badge-disetujui" class="hidden bg-green-100 text-green-700 border border-green-300 text-sm px-4 py-2 rounded-lg font-semibold">
            ✅ Sudah Disetujui
        </span>

        {{-- Tombol PDF --}}
        <button onclick="downloadPDF()" class="bg-yellow-500 text-white font-bold text-sm px-5 py-2.5 hover:bg-yellow-600 transition-all duration-200 rounded-lg inline-flex items-center gap-2">
            <i class="fas fa-download"></i> Download PDF
        </button>
    </div>
</div>

{{-- ==================== AREA SURAT ==================== --}}
<div id="area-cetak" class="surat-container max-w-7xl mx-auto px-6 mb-10">
    <div class="bg-white w-[210mm] min-h-[297mm] shadow-lg mx-auto relative" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #000; padding: 20mm 25mm;">
        
        {{-- ==================== KOP SURAT ==================== --}}
        <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 3px solid #000;">
            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                <div style="width: 80px; flex-shrink: 0; padding-right: 10px;">
                    <img src="{{ asset('images/logo-PNC.png') }}" alt="Logo PNC" style="width: 75px; height: auto; object-fit: contain;">
                </div>
                
                <div style="flex-grow: 1; text-align: center;">
                    <p style="font-size: 11pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                        Kementerian Pendidikan Tinggi, Sains, dan Teknologi
                    </p>
                    <h2 style="font-size: 15pt; font-weight: bold; margin: 2px 0; text-transform: uppercase; letter-spacing: 2px;">
                        Politeknik Negeri Cilacap
                    </h2>
                    <p style="font-size: 10pt; margin: 1px 0; font-weight: bold;">
                        Jurusan Komputer dan Bisnis
                    </p>
                </div>
                
                <div style="width: 80px; flex-shrink: 0;"></div>
            </div>
            
            <div style="text-align: center; margin-top: 5px;">
                <p style="font-size: 9pt; margin: 1px 0; font-style: italic;">
                    Jalan Dr. Soetomo No. 1, Sidakaya - Cilacap 53212 Jawa Tengah
                </p>
                <p style="font-size: 9pt; margin: 1px 0;">
                    Telepon: (0282) 533329, Fax: (0282) 537992
                </p>
                <p style="font-size: 9pt; margin: 1px 0;">
                    Website: <span style="color: blue;">www.pnc.ac.id</span>, Email: <span style="color: blue;">sekretariat@pnc.ac.id</span>
                </p>
            </div>
        </div>

        {{-- ==================== JUDUL SURAT ==================== --}}
        <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
            <p style="font-size: 13pt; font-weight: bold; letter-spacing: 2px; margin: 0;">DAFTAR DOSEN VISITASI MAGANG INDUSTRI</p>
            <p style="font-size: 13pt; font-weight: bold; letter-spacing: 2px; margin: 0;">TAHUN AKADEMIK {{ date('Y') }}/{{ date('Y') + 1 }}</p>
        </div>

        {{-- ==================== ISI TABEL ==================== --}}
        <div style="margin-bottom: 20px;">
            @if($previewVisitasi->isEmpty())
                <p style="text-align: center; color: #999; padding: 40px 0;">Belum ada data dosen visitasi yang ditugaskan.</p>
            @else
                @foreach($previewVisitasi as $idDosen => $kelompok)
                    <div style="margin-bottom: 15px;">
                        <p style="font-size: 12pt; font-weight: bold; margin-bottom: 5px;">
                            {{ $loop->iteration }}. {{ $kelompok->first()->dosen->nama ?? '-' }}{{ $kelompok->first()->dosen->nidn ? ' (' . $kelompok->first()->dosen->nidn . ')' : '' }}
                        </p>
                        <table style="width: 100%; border-collapse: collapse; font-size: 11pt;">
                            <thead>
                                <tr style="background-color: #f0f0f0;">
                                    <th style="border: 1px solid #000; padding: 6px; width: 40px; text-align: center;">NO</th>
                                    <th style="border: 1px solid #000; padding: 6px; width: 100px; text-align: center;">NPM</th>
                                    <th style="border: 1px solid #000; padding: 6px; text-align: left;">NAMA MAHASISWA</th>
                                    <th style="border: 1px solid #000; padding: 6px; text-align: left;">PERUSAHAAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kelompok as $idx => $mhs)
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $idx + 1 }}</td>
                                    <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $mhs->mahasiswa->npm ?? '-' }}</td>
                                    <td style="border: 1px solid #000; padding: 5px 10px;">{{ $mhs->mahasiswa->nama ?? '-' }}</td>
                                    <td style="border: 1px solid #000; padding: 5px 10px;">{{ $mhs->perusahaan->nama_perusahaan ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        </div>

{{-- ==================== TANDA TANGAN ==================== --}}
{{-- ✅ FIX: Hanya tampilkan TTD jika ada data visitasi --}}
@if(!$previewVisitasi->isEmpty())
<div style="margin-top: 30px; text-align: right;">
    <div style="display: inline-block; text-align: center; width: 280px;">
        <p style="margin: 0; font-size: 12pt;">Cilacap, {{ $tanggalSurat }}</p>
        <p style="margin: 0; font-size: 12pt; font-weight: bold;">Ketua Jurusan Komputer dan Bisnis</p>
        <p style="margin: 0; font-size: 12pt; margin-bottom: 10px;">Politeknik Negeri Cilacap,</p>
        
        <div style="height: 20px;"></div>

        <div class="ttd-content">
            @if(($sudahDiAccKajur ?? false) && $ketuaJurusan && $ketuaJurusan->ttd_digital)
                <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                    <img src="{{ asset('storage/ttd_digital/' . $ketuaJurusan->ttd_digital) }}" 
                         alt="TTD Kajur" class="ttd-bersih"
                         style="width: 200px; height: auto; max-height: 100px; object-fit: contain;">
                </div>
                <p style="margin: 0; font-size: 11pt; text-align: center;">
                    <strong><u>{{ $ketuaJurusan->nama }}</u></strong><br>
                    NIP. {{ $ketuaJurusan->nip }}
                </p>
            @else
                <div style="height: 60px;"></div>
                <p style="margin: 0; font-size: 11pt; font-weight: bold; text-align: center; text-decoration: underline;">{{ $ketuaJurusan->nama ?? '................................' }}</p>
                @if($ketuaJurusan && $ketuaJurusan->nip)
                    <p style="margin: 0; font-size: 10pt; text-align: center;">NIP. {{ $ketuaJurusan->nip }}</p>
                @else
                    <p style="margin: 0; font-size: 10pt; text-align: center;">NIP. ................................</p>
                @endif
            @endif
        </div>
    </div>
</div>
@endif
    </div>
</div>

{{-- ==================== MODAL KONFIRMASI KAJUR ==================== --}}
{{-- ✅ HANYA RENDER JIKA KAJUR BISA APPROVE + $idProdi ADA --}}
@if(($bisaDiApprove ?? false) && isset($idProdi))
<div id="modalKonfirmasi" style="display:none;" class="fixed inset-0 bg-black/60 z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
        
        {{-- HEADER --}}
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Konfirmasi Penugasan Dosen Visitasi
            </h3>
        </div>

        {{-- BODY --}}
        <div class="p-6 space-y-5">

            {{-- PILIHAN SETUJUI / TOLAK --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Aksi:</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="status_approval" value="disetujui" class="peer hidden" required onchange="handleRadioChange()">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                            <svg class="w-8 h-8 mx-auto text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="font-semibold text-green-700">Disetujui</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status_approval" value="ditolak" class="peer hidden" required onchange="handleRadioChange()">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-red-500 peer-checked:bg-red-50 transition">
                            <svg class="w-8 h-8 mx-auto text-red-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="font-semibold text-red-700">Ditolak</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- CHECKBOX TTD --}}
            <div id="ttd-checkbox-area">
                <label class="flex items-start gap-3 cursor-pointer select-none bg-gray-50 border border-gray-200 rounded-lg p-4 hover:bg-gray-100 transition" id="label_ttd">
                    <input type="checkbox" id="cek_ttd" class="mt-0.5 w-5 h-5 text-gray-600 border-gray-300 rounded focus:ring-gray-400 shrink-0">
                    <div>
                        <span class="text-sm font-semibold text-gray-800">Cantumkan TTD Digital pada surat</span>
                        <p class="text-xs text-gray-500 mt-0.5">Centang untuk menampilkan tanda tangan digital Anda di surat.</p>
                    </div>
                </label>
            </div>

            {{-- CATATAN OPSIONAL --}}
            <div>
                <label for="catatan_kajur" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <textarea id="catatan_kajur" 
                          name="catatan_kajur" 
                          rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                          placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>

        </div>

        {{-- FOOTER --}}
        <div class="p-6 border-t border-gray-200 flex gap-3">
            <button type="button" onclick="closeModalKonfirmasi()" class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg hover:bg-gray-50 transition">
                ← Batal
            </button>
            <button type="button" onclick="kirimApproval()" class="flex-1 bg-yellow-500 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-yellow-600 transition" id="btnKirim">
                Kirim
            </button>
        </div>

    </div>
</div>
@endif

@endsection

{{-- ==================== CSS ==================== --}}
<style>
    .surat-container { background: transparent; }
.ttd-bersih {
    filter: grayscale(100%) contrast(700%) brightness(120%);
}
    
    @media print {
        nav, footer, .no-print, .sidebar, .navbar, header { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .surat-container { box-shadow: none !important; padding: 0 !important; max-width: 100% !important; margin: 0 !important; }
        .ttd-bersih {
    filter: grayscale(100%) contrast(700%) brightness(120%) !important;
}
        @page { size: A4; margin: 0; }
    }
</style>

{{-- ==================== SCRIPT HTML2PDF ==================== --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    // ==================== DOWNLOAD PDF ====================
    function bersihkanGambarTTD(imgElement) {
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = imgElement.naturalWidth;
            canvas.height = imgElement.naturalHeight;
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.src = imgElement.src;
            img.onload = function() {
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imageData.data;
                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i], g = data[i + 1], b = data[i + 2];
                    if (r > 150 && g > 150 && b > 150) {
                        data[i] = 255; data[i + 1] = 255; data[i + 2] = 255;
                    } else {
                        data[i] = 0; data[i + 1] = 0; data[i + 2] = 0;
                    }
                }
                ctx.putImageData(imageData, 0, 0);
                resolve(canvas.toDataURL('image/png'));
            };
            img.onerror = function() { resolve(imgElement.src); };
        });
    }

    async function downloadPDF() {
        const element = document.getElementById('area-cetak');
        const ttdImages = element.querySelectorAll('.ttd-bersih');
        const originalSrcs = [];
        const originalFilters = [];
        
        for (let i = 0; i < ttdImages.length; i++) {
            const img = ttdImages[i];
            originalSrcs[i] = img.src;
            originalFilters[i] = img.style.filter;
            const cleanSrc = await bersihkanGambarTTD(img);
            img.src = cleanSrc;
            img.style.filter = 'none';
        }
        
        await new Promise(resolve => setTimeout(resolve, 500));
        
        const opt = {
            margin:       0, 
            filename:     'Surat-Daftar-Dosen-Visitasi.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, useCORS: true, scrollY: 0,
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            for (let i = 0; i < ttdImages.length; i++) {
                ttdImages[i].src = originalSrcs[i];
                ttdImages[i].style.filter = originalFilters[i];
            }
        });
    }
</script>

{{-- ==================== MODAL KONFIRMASI JS (HANYA UNTUK KAJUR) ==================== --}}
@if(($bisaDiApprove ?? false) && isset($idProdi))
<script>
    const approvalUrl = '{{ route("kajur.dosen-visitasi.proses_approval_per_prodi", $idProdi) }}';
    const indexUrl = '{{ route("kajur.pembagian-dosen-visitasi.index") }}';

    // ✅ BUKA MODAL
    function openModalKonfirmasi() {
        const modal = document.getElementById('modalKonfirmasi');
        if (!modal) {
            console.error('Modal tidak ditemukan!');
            return;
        }
        
        // Reset semua form
        document.querySelectorAll('input[name="status_approval"]').forEach(r => r.checked = false);
        document.getElementById('catatan_kajur').value = '';
        
        const cekTtd = document.getElementById('cek_ttd');
        const labelTtd = document.getElementById('label_ttd');
        cekTtd.checked = false;
        cekTtd.disabled = false;
        labelTtd.classList.remove('opacity-50', 'pointer-events-none');

        // Tampilkan modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // ✅ TUTUP MODAL
    function closeModalKonfirmasi() {
        const modal = document.getElementById('modalKonfirmasi');
        if (!modal) return;
        
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // ✅ Pilih Ditolak → auto uncheck & disable checkbox TTD
    function handleRadioChange() {
        const cekTtd = document.getElementById('cek_ttd');
        const labelTtd = document.getElementById('label_ttd');
        const selected = document.querySelector('input[name="status_approval"]:checked');
        
        if (selected && selected.value === 'ditolak') {
            cekTtd.checked = false;
            cekTtd.disabled = true;
            labelTtd.classList.add('opacity-50', 'pointer-events-none');
        } else {
            cekTtd.disabled = false;
            labelTtd.classList.remove('opacity-50', 'pointer-events-none');
        }
    }

    // ✅ KIRIM APPROVAL VIA AJAX
    function kirimApproval() {
        const selected = document.querySelector('input[name="status_approval"]:checked');
        if (!selected) {
            alert('Silakan pilih Disetujui atau Ditolak terlebih dahulu.');
            return;
        }

        const cekTtd = document.getElementById('cek_ttd');
        
        // Wajib centang TTD kalau pilih Disetujui
        if (selected.value === 'disetujui' && !cekTtd.checked) {
            alert('Silakan centang "Cantumkan TTD Digital" terlebih dahulu.');
            return;
        }

        const btn = document.getElementById('btnKirim');
        const originalText = btn.textContent;
        const status = selected.value;
        const pakaiTtd = cekTtd.checked;
        const catatan = document.getElementById('catatan_kajur').value;

        btn.disabled = true;
        btn.textContent = 'Memproses...';
        btn.style.opacity = '0.6';

        fetch(approvalUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                status_approval: status,
                pakai_ttd: pakaiTtd,
                catatan: catatan
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('Server error: ' + res.status);
            return res.json();
        })
        .then(data => {
            closeModalKonfirmasi();

            if (data.disetujui) {
                tampilkanTtd(data);
            } else {
                // Ditolak → kembali ke index Kajur
                window.location.href = indexUrl;
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Terjadi kesalahan. Silakan coba lagi.\n\nDetail: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
            btn.style.opacity = '1';
        });
    }

    // ✅ Tampilkan TTD di surat + ubah tombol jadi badge
    function tampilkanTtd(data) {
        // Sembunyikan tombol Konfirmasi, tampilkan badge Sudah Disetujui
        document.getElementById('btn-approve-group').style.display = 'none';
        document.getElementById('badge-disetujui').classList.remove('hidden');

        let ttdHtml;
        
        if (data.pakai_ttd && data.ttd_url) {
            ttdHtml = `
                <div style="display: flex; justify-content: flex-end; margin-bottom: 5px;">
                    <img src="${data.ttd_url}" alt="TTD Kajur" class="ttd-bersih" style="width: 180px; height: auto; max-height: 90px; object-fit: contain;">
                </div>
                <p style="margin: 0; font-size: 10pt;">
                    <strong><u>${data.nama_kajur}</u></strong><br>
                    NIP. ${data.nip_kajur}
                </p>
            `;
        } else {
            ttdHtml = `
                <div style="height: 60px;"></div>
                <p style="margin: 0; font-size: 12pt; font-weight: bold; text-decoration: underline;">${data.nama_kajur}</p>
                <p style="margin: 0; font-size: 10pt;">NIP. ${data.nip_kajur}</p>
            `;
        }

        // Update semua area TTD di surat
        document.querySelectorAll('.ttd-content').forEach(el => {
            el.innerHTML = ttdHtml;
        });
    }

    // ✅ Klik luar modal → tutup
    document.getElementById('modalKonfirmasi').addEventListener('click', function(e) {
        if (e.target === this) closeModalKonfirmasi();
    });

    // ✅ ESC → tutup modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModalKonfirmasi();
    });
</script>
@endif