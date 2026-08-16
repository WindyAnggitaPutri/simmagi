@extends('layouts.app')

@section('title', 'Absensi Magang')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.absensi-wrapper {
    display: none;
    margin-bottom: 24px;
}
.absensi-wrapper.active { display: block; }

.absensi-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.panel-left {
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #d1d5db;
    position: relative;
    min-height: 400px;
}

#liveMode video {
    width: 100%;
    display: block;
    transform: scaleX(-1);
    max-height: 480px;
    object-fit: cover;
}
#liveMode canvas { display: none; }

.cam-overlay {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    transform: scaleX(-1);
}
.face-box {
    position: absolute;
    border: 2px solid #fff;
    border-radius: 4px;
    transition: all 0.15s ease;
    display: none;
}
.face-box.show { display: block !important; }
.face-box.detecting { border-style: dashed; }
.face-box.verified { border-style: solid; }

.no-face-hint {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -70%);
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    text-align: center;
    pointer-events: none;
    display: none;
}
.no-face-hint.show { display: block; }

.cam-info {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 16px 20px;
    background: linear-gradient(transparent, rgba(0,0,0,0.85));
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.cam-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: white;
    padding: 8px 16px;
    background: rgba(0,0,0,0.5);
    border-radius: 4px;
    width: 100%;
    justify-content: center;
}
.status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #999;
    flex-shrink: 0;
}
.status-dot.active { background: #fff; animation: pulse-dot 1.5s infinite; }
.status-dot.detecting { background: #ccc; animation: pulse-dot 1s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:0.3} }

.cam-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}
.btn-icon {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.btn-icon:hover { background: rgba(255,255,255,0.25); }

#capturedMode {
    width: 100%;
    height: 100%;
    min-height: 400px;
    display: none;
    position: relative;
}
#capturedMode img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.captured-badge {
    position: absolute;
    top: 16px; left: 16px;
    padding: 8px 14px;
    background: rgba(255,255,255,0.9);
    color: #111;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.panel-right {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #d1d5db;
    display: flex;
    flex-direction: column;
    min-height: 400px;
}
.map-header {
    padding: 14px 18px;
    background: #f3f4f6;
    color: #111;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.map-header svg { width: 20px; height: 20px; flex-shrink: 0; }
.map-header-text { display: flex; flex-direction: column; }
.map-header-text .label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.map-header-text .status { font-size: 14px; font-weight: 600; }

.map-body { flex: 1; position: relative; }
#locationMap { width: 100%; height: 100%; min-height: 300px; z-index: 1; }

.map-footer {
    padding: 12px 16px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}
.map-coords {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 6px;
}
.map-coords svg { width: 14px; height: 14px; flex-shrink: 0; }
.map-address {
    font-size: 13px;
    color: #374151;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.map-address.loading { color: #9ca3af; font-style: italic; }
.map-accuracy {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 4px;
    margin-top: 6px;
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #e5e7eb;
}

.info-section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #d1d5db;
}
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
}
.info-card {
    background: #f9fafb;
    border-radius: 6px;
    padding: 12px 14px;
    border: 1px solid #e5e7eb;
}
.info-card .label {
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}
.info-card .value {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
}
.info-card .value.muted { color: #9ca3af; font-weight: 400; }

.jarak-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 13px;
    font-weight: 600;
    background: #f9fafb;
    color: #374151;
    border: 1px solid #e5e7eb;
}
.jarak-indicator svg { width: 18px; height: 18px; flex-shrink: 0; }

.info-actions {
    display: flex;
    gap: 12px;
}
.btn-action {
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}
.btn-save {
    flex: 1;
}
.btn-save.enabled {
    background: #111827;
    color: white;
}
.btn-save.enabled:hover {
    background: #374151;
}
.btn-save.disabled {
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}
.btn-retake {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
}
.btn-retake:hover { background: #f3f4f6; }

.cam-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    color: rgba(255,255,255,0.6);
    gap: 12px;
}
.spinner-lg {
    width: 40px; height: 40px;
    border: 3px solid rgba(255,255,255,0.2);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
.map-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: absolute;
    inset: 0;
    color: #9ca3af;
    gap: 10px;
    z-index: 2;
    background: white;
}
.spinner-map {
    width: 32px; height: 32px;
    border: 3px solid #e5e7eb;
    border-top-color: #111827;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.spinner-sm {
    width: 20px; height: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.notif-banner {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 14px;
    color: #374151;
    background: #f9fafb;
}

.service-warning {
    display: none;
    padding: 12px 20px;
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    color: #374151;
    font-size: 14px;
    margin-bottom: 16px;
}
.service-warning.active { display: flex; align-items: center; gap: 8px; }

.flash-msg {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 14px;
    color: #374151;
    background: #f9fafb;
}

@media (max-width: 768px) {
    .absensi-grid { grid-template-columns: 1fr; }
    .panel-right { min-height: 280px; }
    #locationMap { min-height: 200px; }
    .info-grid { grid-template-columns: 1fr; }
    .info-actions { flex-direction: column; }
}
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Absensi Magang</h1>
            <p class="text-sm text-gray-500 mt-1">
                Total bulan ini: {{ $totalAbsensiBulanIni }} hari
                |
                Hadir: {{ $totalHadirBulanIni }} hari
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if(!$pengajuanMagang)
                <span class="text-sm text-gray-500 bg-gray-100 border border-gray-200 px-4 py-2 rounded">Belum ada absensi</span>
                //ini button absennya
           @elseif(!$tipeAbsenSekarang && $statusHariIni === 'lengkap')  
    
            @elseif(!$tipeAbsenSekarang && $statusHariIni === 'non_hadir')
                <span class="text-sm text-gray-500 bg-gray-100 border border-gray-200 px-4 py-2 rounded">Status hari ini: {{ ucfirst($nonHadirHariIni->status) }}</span>
            @else
                <button onclick="toggleCamera()" id="btnToggleCamera"
                        class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-white px-5 py-2.5 rounded font-medium transition-colors">
                    <span id="toggleCameraText">Absen {{ ucfirst($tipeAbsenSekarang) }}</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Notifikasi absensi hari ini -->
    @if($statusHariIni === 'belum_masuk')
    <div class="notif-banner">Anda belum absen masuk hari ini.</div>
    @elseif($statusHariIni === 'belum_pulang')
    <div class="notif-banner">Anda belum absen pulang hari ini.</div>
    @endif

    <!-- Service Warning -->
    <div id="serviceWarning" class="service-warning">
        <span>Face detection service sedang offline. Absensi hanya bisa dilakukan saat service aktif.</span>
    </div>

    @if(session('success'))
    <div class="flash-msg">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="flash-msg">{{ session('error') }}</div>
    @endif

    <!-- Kamera + Peta -->
    <div class="absensi-wrapper" id="absensiWrapper">
        <div class="absensi-grid">

            <!-- KIRI: Kamera -->
            <div class="panel-left" id="panelLeft">

                <div id="liveMode">
                    <div class="cam-loading" id="camLoading">
                        <div class="spinner-lg"></div>
                        <span>Mengaktifkan kamera...</span>
                    </div>
                    <video id="camVideo" autoplay playsinline muted style="display:none;"></video>
                    <canvas id="camCanvas"></canvas>
                    <div class="cam-overlay" id="camOverlay" style="display:none;">
                        <div id="faceBox" class="face-box"></div>
                        <div class="no-face-hint" id="noFaceHint">Hadapkan wajah ke kamera</div>
                    </div>
                    <div class="cam-info" id="camInfo" style="display:none;">
                        <div class="cam-status">
                            <div class="status-dot" id="statusDot"></div>
                            <span id="statusText">Mendeteksi wajah...</span>
                        </div>
                        <div class="cam-actions">
                            <button class="btn-icon" onclick="switchCamera()" title="Ganti Kamera">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                            <button class="btn-icon" onclick="toggleCamera()" title="Tutup Kamera">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="capturedMode">
                    <img id="capturedImg" alt="Foto absensi">
                    <div class="captured-badge">Wajah Terverifikasi</div>
                </div>
            </div>

            <!-- KANAN: Peta -->
            <div class="panel-right">
                <div class="map-header">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div class="map-header-text">
                        <span class="label">Lokasi Anda</span>
                        <span class="status" id="mapStatus">Mendeteksi GPS...</span>
                    </div>
                </div>
                <div class="map-body">
                    <div class="map-loading" id="mapLoading">
                        <div class="spinner-map"></div>
                        <span>Mengambil lokasi GPS...</span>
                    </div>
                    <div id="locationMap" style="display:none;"></div>
                </div>
                <div class="map-footer">
                    <div class="map-coords" id="mapCoords" style="display:none;">
                        <!-- <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg> -->
                        <span id="coordsText">-</span>
                    </div>
                    <div class="map-address loading" id="mapAddress">Mencari alamat...</div>
                    <div id="mapAccuracy" style="display:none;"></div>
                </div>
            </div>

        </div>

        <!-- Info + Tombol -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-card">
                    <div class="label">Lokasi</div>
                    <div class="value muted" id="infoLokasi">Menunggu GPS...</div>
                </div>
                <div class="info-card">
                    <div class="label">Perusahaan</div>
                    <div class="value">{{ $namaPerusahaan }}</div>
                </div>
                <div class="info-card">
                    <div class="label">Jarak ke Perusahaan</div>
                    <div class="value muted" id="infoJarak">Menunggu data...</div>
                </div>
                <div class="info-card">
                    <div class="label">Waktu</div>
                    <div class="value muted" id="infoWaktu">-</div>
                </div>
            </div>
            <div class="jarak-indicator" id="jarakIndicator">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="jarakText">Menunggu pengambilan foto otomatis...</span>
            </div>
            <div class="info-actions">
                <button onclick="retakePhoto()" class="btn-action btn-retake" id="btnRetake" style="display:none;">Ambil Ulang</button>
                <button onclick="submitAbsensi()" id="btnSubmit" class="btn-action btn-save disabled" disabled>
                    <span id="submitText">Simpan Absen {{ ucfirst($tipeAbsenSekarang ?? 'masuk') }}</span>
                    <div id="submitSpinner" class="spinner-sm" style="display:none;"></div>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Riwayat Absensi</h2>
        </div>
        @if($riwayatAbsensi->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jam Masuk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jam Pulang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Jarak Masuk</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Jarak Pulang</th>
                        <!-- <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Validasi</th> -->
                                                <!-- <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Logbook</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr> -->
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($riwayatAbsensi as $index => $absensi)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3.5 text-sm text-gray-600">{{ ($riwayatAbsensi->currentPage() - 1) * $riwayatAbsensi->perPage() + $index + 1 }}</td>
                        <td class="px-4 py-3.5 text-sm text-gray-800">{{ $absensi->tanggal_formatted }}</td>
                        <td class="px-4 py-3.5 text-sm text-gray-600">{{ $absensi->jam_masuk_formatted }}</td>
                        <td class="px-4 py-3.5 text-sm text-gray-600">{{ $absensi->jam_pulang_formatted }}</td>
                        <td class="px-4 py-3.5 text-sm text-gray-600 text-center">{{ $absensi->status_text }}</td>
                        <td class="px-4 py-3.5 text-sm text-gray-600 text-center">{{ $absensi->jarak_masuk_tampil }}</td>
                        <td class="px-4 py-3.5 text-sm text-gray-600 text-center">{{ $absensi->jarak_pulang_tampil }}</td>
                        <!-- <td class="px-4 py-3.5 text-sm text-gray-600 text-center">{{ $absensi->validasi_text }}</td> -->
                                                <!-- <td class="px-4 py-3.5 text-sm text-center">
                            @if($absensi->status === 'hadir' && $absensi->jam_pulang)
                                @if($absensi->logbook_filled)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full">
                                        <i class="fas fa-check-circle text-green-500"></i> Sudah
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-2.5 py-1 rounded-full">
                                        <i class="fas fa-exclamation-circle text-red-500"></i> Belum
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td> -->
                        <!-- <td class="px-4 py-3.5 text-sm text-center">
                            @if($absensi->status === 'hadir' && $absensi->jam_pulang)
                                @if($absensi->logbook_filled && $absensi->logbook_data)
                                    <a href="{{ route('mahasiswa.logbook.detail', $absensi->logbook_data->id_logbook) }}" 
                                       class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 px-2 py-1 rounded font-medium text-xs transition-colors">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                @else
                                    <a href="{{ route('mahasiswa.logbook.create', ['from_absensi' => $absensi->id_absensi]) }}" 
                                       class="inline-flex items-center gap-1 text-yellow-600 hover:text-yellow-700 px-2 py-1 rounded font-medium text-xs transition-colors">
                                        <i class="fas fa-pen"></i> Isi Logbook
                                    </a>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td> -->
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex justify-center">{{ $riwayatAbsensi->appends(request()->query())->links() }}</div>
        </div>
        @else
        <div class="py-16 px-6 text-center">
            <h3 class="text-lg font-semibold text-gray-700 mb-1">Belum Ada Riwayat Absensi</h3>
            <p class="text-sm text-gray-500">Buka kamera untuk mulai absen pertama kamu</p>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const DETECT_URL = '{{ route("mahasiswa.absensi.detect") }}';
const CHECK_URL  = '{{ route("mahasiswa.absensi.check-service") }}';
const STORE_URL  = '{{ route("mahasiswa.absensi.store-index") }}';
const DETECT_INTERVAL = 300;
const PERUSAHAAN_COORDS = @json($perusahaanCoords);
const MAX_JARAK = 100;
const TIPE_ABSEN = '{{ $tipeAbsenSekarang ?? "masuk" }}';

let stream = null, detectionLoop = null, watchId = null;
let faceVerified = false, autoCaptureDone = false;
let capturedImageData = null, jarakMeter = null;
let currentFacingMode = 'user';
let locationData = { latitude: null, longitude: null, address: null, accuracy: null };
let cameraActive = false;
let mainMap = null, mainMarker = null, mainCircle = null;

const $ = id => document.getElementById(id);

async function checkFaceService() {
    try {
        const r = await fetch(CHECK_URL);
        if (!r.ok) return false;
        const d = await r.json();
        return d.online === true;
    } catch(e) { return false; }
}

async function toggleCamera() {
    cameraActive ? stopCamera() : await startCameraFlow();
}

async function startCameraFlow() {
    if (!(await checkFaceService())) {
        $('serviceWarning').classList.add('active');
        return;
    }
    $('serviceWarning').classList.remove('active');

    faceVerified = false;
    autoCaptureDone = false;
    capturedImageData = null;
    jarakMeter = null;

    $('absensiWrapper').classList.add('active');
    $('liveMode').style.display = '';
    $('capturedMode').style.display = 'none';
    resetInfoUI();

    $('camLoading').style.display = 'flex';
    $('camLoading').innerHTML = '<div class="spinner-lg"></div><span>Mengaktifkan kamera...</span>';
    $('camVideo').style.display = 'none';
    $('camInfo').style.display = 'none';
    $('camOverlay').style.display = 'none';

    $('mapLoading').style.display = 'flex';
    $('mapLoading').innerHTML = '<div class="spinner-map"></div><span>Mengambil lokasi GPS...</span>';
    $('locationMap').style.display = 'none';
    $('mapCoords').style.display = 'none';
    $('mapAddress').textContent = 'Mencari alamat...';
    $('mapAddress').classList.add('loading');
    $('mapAccuracy').style.display = 'none';
    $('mapStatus').textContent = 'Mendeteksi GPS...';

    cameraActive = true;
    $('toggleCameraText').textContent = 'Tutup Kamera';
    $('btnToggleCamera').classList.remove('bg-gray-800','hover:bg-gray-700');
    $('btnToggleCamera').classList.add('bg-gray-600','hover:bg-gray-500');

    await startStream();
    startLocationWatch();
}

function resetInfoUI() {
    $('infoLokasi').textContent = 'Menunggu GPS...';
    $('infoLokasi').className = 'value muted';
    $('infoJarak').textContent = 'Menunggu data...';
    $('infoJarak').className = 'value muted';
    $('infoWaktu').textContent = '-';
    $('infoWaktu').className = 'value muted';
    $('jarakText').textContent = 'Menunggu pengambilan foto otomatis...';
    $('btnSubmit').className = 'btn-action btn-save disabled';
    $('btnSubmit').disabled = true;
    $('submitText').textContent = 'Simpan Absen ' + TIPE_ABSEN.charAt(0).toUpperCase() + TIPE_ABSEN.slice(1);
    $('submitSpinner').style.display = 'none';
    $('btnRetake').style.display = 'none';
}

async function startStream() {
    try {
        if (stream) stream.getTracks().forEach(t => t.stop());
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: currentFacingMode, width: {ideal:640}, height: {ideal:480} },
            audio: false
        });
        $('camVideo').srcObject = stream;
        await new Promise(r => { $('camVideo').onloadedmetadata = r; });
        $('camLoading').style.display = 'none';
        $('camVideo').style.display = 'block';
        $('camInfo').style.display = 'flex';
        $('camOverlay').style.display = 'block';
        $('statusDot').className = 'status-dot detecting';
        $('statusText').textContent = 'Mendeteksi wajah...';
        startDetectionLoop();
    } catch(e) {
        console.error('Camera error:', e);
        $('camLoading').innerHTML = '<span>Gagal mengakses kamera</span>';
    }
}

function stopCamera() {
    if (detectionLoop) { clearInterval(detectionLoop); detectionLoop = null; }
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    if (watchId !== null) { navigator.geolocation.clearWatch(watchId); watchId = null; }
    faceVerified = false;
    autoCaptureDone = false;
    capturedImageData = null;
    cameraActive = false;
    $('absensiWrapper').classList.remove('active');
    $('toggleCameraText').textContent = 'Absen ' + TIPE_ABSEN.charAt(0).toUpperCase() + TIPE_ABSEN.slice(1);
    $('btnToggleCamera').classList.remove('bg-gray-600','hover:bg-gray-500');
    $('btnToggleCamera').classList.add('bg-gray-800','hover:bg-gray-700');
    if (mainMap) { mainMap.remove(); mainMap = null; mainMarker = null; mainCircle = null; }
}

async function switchCamera() {
    currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
    if (detectionLoop) clearInterval(detectionLoop);
    faceVerified = false;
    autoCaptureDone = false;
    await startStream();
}

function startDetectionLoop() {
    if (detectionLoop) clearInterval(detectionLoop);
    detectionLoop = setInterval(async () => {
        if ($('camVideo').readyState < 2) return;
        if (autoCaptureDone) { clearInterval(detectionLoop); detectionLoop = null; return; }
        await detectFace();
    }, DETECT_INTERVAL);
}

async function detectFace() {
    const c = $('camCanvas'), ctx = c.getContext('2d');
    c.width = $('camVideo').videoWidth;
    c.height = $('camVideo').videoHeight;
    ctx.drawImage($('camVideo'), 0, 0);
    const data = c.toDataURL('image/jpeg', 0.7);
    try {
        const r = await fetch(DETECT_URL, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ image: data })
        });
        const res = await r.json();
        if (res.success) updateFaceUI(res);
    } catch(e) {
        $('statusText').textContent = 'Koneksi ke service terputus';
        $('statusDot').className = 'status-dot';
    }
}

function updateFaceUI(res) {
    const fb = $('faceBox'), panel = $('panelLeft');
    if (res.face_detected) {
        fb.classList.add('show');
        $('noFaceHint').classList.remove('show');
        if (res.face_box) {
            const sx = panel.offsetWidth / $('camVideo').videoWidth;
            const sy = $('camVideo').offsetHeight / $('camVideo').videoHeight;
            fb.style.left   = (res.face_box.x * sx) + 'px';
            fb.style.top    = (res.face_box.y * sy) + 'px';
            fb.style.width  = (res.face_box.w * sx) + 'px';
            fb.style.height = (res.face_box.h * sy) + 'px';
        }
        if (res.is_live) {
            fb.className = 'face-box show verified';
            $('statusDot').className = 'status-dot active';
            $('statusText').textContent = 'Wajah terverifikasi. Mengambil foto otomatis...';
            faceVerified = true;
            if (!autoCaptureDone) {
                autoCaptureDone = true;
                setTimeout(doCapture, 400);
            }
        } else {
            fb.className = 'face-box show detecting';
            $('statusDot').className = 'status-dot detecting';
            $('statusText').textContent = res.message || 'Mendeteksi...';
        }
    } else {
        fb.classList.remove('show');
        fb.className = 'face-box';
        $('noFaceHint').classList.add('show');
        $('statusDot').className = 'status-dot';
        $('statusText').textContent = res.message || 'Wajah tidak terdeteksi';
    }
}

function doCapture() {
    if (!faceVerified) return;
    const c = document.createElement('canvas'), ctx = c.getContext('2d');
    c.width = $('camVideo').videoWidth;
    c.height = $('camVideo').videoHeight;
    ctx.translate(c.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage($('camVideo'), 0, 0);
    capturedImageData = c.toDataURL('image/jpeg', 0.9);

    if (detectionLoop) { clearInterval(detectionLoop); detectionLoop = null; }
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }

    $('liveMode').style.display = 'none';
    $('capturedMode').style.display = 'block';
    $('capturedImg').src = capturedImageData;

    const now = new Date();
    $('infoWaktu').textContent = now.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long',year:'numeric'}) + ', ' + now.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    $('infoWaktu').className = 'value';
    if (locationData.address) {
        $('infoLokasi').textContent = locationData.address;
    } else if (locationData.latitude) {
        $('infoLokasi').textContent = locationData.latitude.toFixed(6) + ', ' + locationData.longitude.toFixed(6);
    } else {
        $('infoLokasi').textContent = 'Lokasi belum tersedia';
    }
    $('infoLokasi').className = 'value';

    $('btnRetake').style.display = '';
    evaluateJarak();
}

function evaluateJarak() {
    if (!capturedImageData) return;
    const ji = $('jarakIndicator'), jt = $('jarakText'), btn = $('btnSubmit'), je = $('infoJarak');

    if (!PERUSAHAAN_COORDS || !PERUSAHAAN_COORDS.lat) {
        je.textContent = 'Data perusahaan tidak tersedia';
        je.className = 'value';
        jt.textContent = 'Koordinat perusahaan belum terdaftar di sistem. Hubungi admin.';
        btn.className = 'btn-action btn-save disabled';
        btn.disabled = true;
        return;
    }
    if (!locationData.latitude) {
        je.textContent = 'Lokasi belum didapat';
        je.className = 'value muted';
        jt.textContent = 'Menunggu lokasi GPS...';
        btn.className = 'btn-action btn-save disabled';
        btn.disabled = true;
        return;
    }

    jarakMeter = haversine(locationData.latitude, locationData.longitude, PERUSAHAAN_COORDS.lat, PERUSAHAAN_COORDS.lng);
    je.textContent = jarakMeter.toFixed(1) + ' meter';
    je.className = 'value';

    if (jarakMeter <= MAX_JARAK) {
        jt.textContent = 'Anda berada dalam radius ' + MAX_JARAK + ' meter dari lokasi perusahaan. Absensi diizinkan.';
        btn.className = 'btn-action btn-save enabled';
        btn.disabled = false;
    } else {
        jt.textContent = 'Jarak Anda ' + jarakMeter.toFixed(1) + ' meter dari perusahaan. Absensi hanya diizinkan dalam radius ' + MAX_JARAK + ' meter.';
        btn.className = 'btn-action btn-save disabled';
        btn.disabled = true;
    }
}

function haversine(lat1,lon1,lat2,lon2) {
    const R=6371000, dLat=(lat2-lat1)*Math.PI/180, dLon=(lon2-lon1)*Math.PI/180;
    const a=Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
    return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
}

async function retakePhoto() {
    capturedImageData = null;
    autoCaptureDone = false;
    jarakMeter = null;
    resetInfoUI();

    $('capturedMode').style.display = 'none';
    $('liveMode').style.display = '';
    $('camVideo').style.display = 'block';
    $('camInfo').style.display = 'flex';
    $('camOverlay').style.display = 'block';
    $('statusDot').className = 'status-dot detecting';
    $('statusText').textContent = 'Mendeteksi wajah...';
    $('faceBox').className = 'face-box';
    $('faceBox').classList.remove('show');
    $('noFaceHint').classList.add('show');

    await startStream();
}

function startLocationWatch() {
    if (!navigator.geolocation) {
        $('mapStatus').textContent = 'GPS tidak didukung';
        $('mapLoading').innerHTML = '<span>Browser tidak mendukung GPS</span>';
        return;
    }
    watchId = navigator.geolocation.watchPosition(pos => {
        locationData.latitude = pos.coords.latitude;
        locationData.longitude = pos.coords.longitude;
        locationData.accuracy = pos.coords.accuracy;
        updateMapUI(pos.coords);
        reverseGeocode(pos.coords.latitude, pos.coords.longitude);
        if ($('absensiWrapper').classList.contains('active')) {
            if (locationData.address) {
                $('infoLokasi').textContent = locationData.address;
            } else {
                $('infoLokasi').textContent = locationData.latitude.toFixed(6) + ', ' + locationData.longitude.toFixed(6);
            }
            $('infoLokasi').className = 'value';
            if (capturedImageData) evaluateJarak();
        }
    }, err => {
        let msg = 'Gagal mendapatkan lokasi';
        if (err.code===1) msg='Izin lokasi ditolak. Aktifkan di pengaturan browser.';
        if (err.code===2) msg='GPS tidak tersedia';
        if (err.code===3) msg='Waktu habis mendapatkan lokasi';
        $('mapStatus').textContent = 'Gagal';
        $('mapLoading').innerHTML = '<span style="text-align:center;font-size:13px;">'+msg+'</span>';
        if (capturedImageData) evaluateJarak();
    }, { enableHighAccuracy:true, timeout:15000, maximumAge:0 });
}

function updateMapUI(coords) {
    const lat=coords.latitude, lng=coords.longitude, acc=coords.accuracy;
    $('mapStatus').textContent = 'Lokasi ditemukan';
    $('mapLoading').style.display = 'none';
    $('locationMap').style.display = 'block';
    $('mapCoords').style.display = 'flex';
    $('coordsText').textContent = lat.toFixed(6)+', '+lng.toFixed(6);
    const ae = $('mapAccuracy');
    ae.style.display = 'inline-flex';
    

    if (!mainMap) { initMap(lat,lng,acc); }
    else {
        if (mainMarker) mainMarker.setLatLng([lat,lng]);
        if (mainCircle) mainCircle.setLatLng([lat,lng]).setRadius(acc);
        mainMap.panTo([lat,lng],{animate:true});
    }
}

function initMap(lat,lng,acc) {
    mainMap = L.map('locationMap',{center:[lat,lng],zoom:16,zoomControl:true,attributionControl:false});
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(mainMap);
    const icon = L.divIcon({html:'<div style="width:20px;height:20px;background:#111;border:2px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',className:'',iconSize:[20,20],iconAnchor:[10,10]});
    mainCircle = L.circle([lat,lng],{radius:acc,color:'#111',fillColor:'#111',fillOpacity:0.05,weight:1.5,dashArray:'4,4'}).addTo(mainMap);
    mainMarker = L.marker([lat,lng],{icon}).addTo(mainMap);
    if (PERUSAHAAN_COORDS && PERUSAHAAN_COORDS.lat) {
        const pIcon = L.divIcon({html:'<div style="width:24px;height:24px;background:#fff;border:2px solid #111;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',className:'',iconSize:[24,24],iconAnchor:[12,12]});
        L.marker([PERUSAHAAN_COORDS.lat,PERUSAHAAN_COORDS.lng],{icon:pIcon}).addTo(mainMap).bindPopup('Lokasi Perusahaan');
        L.polyline([[lat,lng],[PERUSAHAAN_COORDS.lat,PERUSAHAAN_COORDS.lng]],{color:'#6b7280',weight:1.5,dashArray:'6,4',opacity:0.7}).addTo(mainMap);
    }
    setTimeout(()=>mainMap.invalidateSize(),200);
}

async function reverseGeocode(lat,lng) {
    try {
        const r = await fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat='+lat+'&lon='+lng+'&zoom=18&addressdetails=1',{headers:{'Accept-Language':'id'}});
        const d = await r.json();
        locationData.address = d.display_name || null;
        const el = $('mapAddress');
        if (locationData.address) { el.textContent=locationData.address; el.classList.remove('loading'); }
        else { el.textContent='Alamat tidak ditemukan'; el.classList.add('loading'); }
        if ($('absensiWrapper').classList.contains('active') && locationData.address) {
            $('infoLokasi').textContent = locationData.address;
            $('infoLokasi').className = 'value';
        }
    } catch(e) { $('mapAddress').textContent='Gagal memuat alamat'; }
}

async function submitAbsensi() {
    if (!capturedImageData) { alert('Foto belum diambil'); return; }
    if (!locationData.latitude) { alert('Lokasi GPS belum didapat.'); return; }
    if (jarakMeter===null || jarakMeter>MAX_JARAK) { alert('Jarak ke perusahaan melebihi '+MAX_JARAK+' meter.'); return; }

    const btn=$('btnSubmit');
    btn.disabled=true;
    $('submitText').textContent='Menyimpan...';
    $('submitSpinner').style.display='block';

    const blob = await (await fetch(capturedImageData)).blob();
    const fd = new FormData();
    fd.append('foto',blob,'absensi.jpg');
    fd.append('latitude',locationData.latitude);
    fd.append('longitude',locationData.longitude);
    fd.append('alamat',locationData.address||'');
    fd.append('catatan','');
    fd.append('face_verified','true');

    try {
        const r = await fetch(STORE_URL,{
            method:'POST',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            body:fd
        });
        const res = await r.json();
        
        if (res.success) {
            // Tampilkan pesan sukses singkat
            stopCamera();
            
            // Redirect langsung ke URL yang diberikan controller
            if (res.redirect) {
                window.location.href = res.redirect;
            } else {
                window.location.reload();
            }
        } else {
            alert(res.message || 'Gagal menyimpan absensi');
            btn.disabled=false;
            $('submitText').textContent='Simpan Absen ' + TIPE_ABSEN.charAt(0).toUpperCase() + TIPE_ABSEN.slice(1);
            $('submitSpinner').style.display='none';
        }
    } catch(e) {
        console.error('Submit error:',e);
        alert('Terjadi kesalahan jaringan');
        btn.disabled=false;
        $('submitText').textContent='Simpan Absen ' + TIPE_ABSEN.charAt(0).toUpperCase() + TIPE_ABSEN.slice(1);
        $('submitSpinner').style.display='none';
    }
}
document.addEventListener('DOMContentLoaded', async () => {
    const online = await checkFaceService();
    $('serviceWarning').classList.toggle('active', !online);
    setInterval(async () => {
        $('serviceWarning').classList.toggle('active', !(await checkFaceService()));
    }, 30000);
});
</script>
@endpush