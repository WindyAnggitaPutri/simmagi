@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Monitoring Dosen Visitasi</h1>
        <p class="text-sm text-gray-500 mt-1">Lihat data dosen visitasi yang sudah disetujui Kajur</p>
    </div>

    {{-- INFO --}}
    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-xs text-blue-700">
            ℹ️ Halaman ini bersifat <strong>view-only</strong>. Upload SK dilakukan oleh BAUP. Prodi yang masih menunggu persetujuan Kajur tidak ditampilkan.
        </p>
    </div>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR --}}
    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- TABLE --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 w-10">No</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Jurusan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama Prodi</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Total Mhs</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">SK Visitasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prodiData as $index => $data)
                    @php
                        $prodi = $data['prodi'];
                        $namaJurusan = $prodi->jurusan->nama_jurusan ?? '-';
                    @endphp
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-center text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $namaJurusan }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $prodi->nama_prodi }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-semibold">{{ $data['total'] }}</span>
                            <span class="text-gray-400 text-xs">orang</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @switch($data['status'])
                                @case('menunggu_baup')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        📋 Menunggu BAUP
                                    </span>
                                    @break
                                @case('diproses')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        🔄 Sedang Diproses
                                    </span>
                                    @break
                                @case('selesai')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Selesai
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        —
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('baak.dosen-visitasi.preview-per-prodi', $prodi->id_prodi) }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 text-white text-xs font-semibold rounded-lg hover:bg-blue-600 transition">
                                👁️ Detail
                            </a>
                        </td>
                        {{-- ✅ SK VISITASI: VIEW ONLY, NO UPLOAD --}}
                        <td class="px-4 py-3 text-center">
                            @if($data['status'] === 'selesai' && $data['file_sk'])
                                <a href="{{ route('baak.dosen-visitasi.lihat_sk', $prodi->id_prodi) }}?from={{ urlencode(url()->current()) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-500 text-white text-xs font-semibold rounded-lg hover:bg-green-600 transition">
                                    📄 Lihat SK
                                </a>
                            @elseif($data['status'] === 'menunggu_baup')
                                <span class="text-xs text-yellow-600 font-medium">Menunggu BAUP</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-400 italic text-sm">Belum ada data dosen visitasi yang disetujui Kajur</p>
                                <p class="text-gray-300 text-xs">Data akan muncul setelah Kajur memberikan persetujuan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- LEGENDA --}}
    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <p class="text-xs font-semibold text-gray-600 mb-2">Keterangan Status:</p>
        <div class="flex flex-wrap gap-4 text-xs text-gray-500">
            <span>📋 Menunggu BAUP = Disetujui Kajur, menunggu proses BAUP</span>
            <span>🔄 Sedang Diproses = Sedang diproses oleh BAUP</span>
            <span>✅ Selesai = SK sudah diupload oleh BAUP</span>
        </div>
    </div>

</div>
@endsection