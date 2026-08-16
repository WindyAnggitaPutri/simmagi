@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

    {{-- BACK --}}
    <a href="{{ route('baak.dosen-pembimbing.index') }}" class="text-yellow-600 font-semibold text-sm hover:underline">
        ← Kembali ke Pengelolaan Dosen Pembimbing
    </a>

    {{-- HEADER --}}
    <div class="mt-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Upload SK Dosen Pembimbing</h1>
        <p class="text-sm text-gray-500 mt-1">
            Prodi: <strong>{{ $prodi->nama_prodi }}</strong> — {{ $prodi->jurusan->nama_jurusan ?? '-' }}
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
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- ==================== UPLOAD AREA ==================== --}}
    <div class="border bg-white mb-6">

        @if($sudahUpload)
            {{-- ==================== SUDAH UPLOAD ==================== --}}
            <div class="px-6 py-4 bg-green-50 border-b border-green-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded border border-green-300">
                        ✅ SK Sudah Diupload
                    </span>
                </div>
               
            </div>

            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                <div class="flex gap-6 text-sm">
                    <div>
                        <span class="text-gray-500">Total Dosen:</span>
                        <span class="font-bold text-gray-800">{{ $totalDosen }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Total Mahasiswa:</span>
                        <span class="font-bold text-gray-800">{{ $totalMhs }}</span>
                    </div>
                </div>
            </div>

            {{-- FORM UPLOAD ULANG --}}
            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 mb-3">Jika perlu mengganti file SK, pilih file baru:</p>
                <form action="{{ route('baak.dosen-pembimbing.upload_sk.store', $prodi->id_prodi) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium mb-2">Upload File SK Baru</label>
                        <input type="file"
                            name="file_sk"
                            accept=".pdf"
                            class="w-full border border-gray-300 text-sm text-gray-500
                                    file:mr-4
                                    file:py-2
                                    file:px-4
                                    file:border-0
                                    file:text-sm
                                    file:font-semibold
                                    file:bg-yellow-500
                                    file:text-white
                                    hover:file:bg-yellow-600"
                            required>
                        <p class="text-xs text-gray-400 mt-1">Format file: PDF (Max 5MB)</p>
                    </div>
                    <div class="flex gap-3 items-center">
                        <button type="submit"
                                class="bg-yellow-500 text-white px-5 py-2 font-semibold hover:bg-yellow-600 transition">
                            📤 Upload Sk Dospem
                        </button>
                    </div>
                </form>
            </div>

        @else
            {{-- ==================== BELUM UPLOAD ==================== --}}
            <div class="px-6 py-4 bg-yellow-50 border-b border-yellow-200">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1.5 rounded border border-yellow-300">
                        ⏳ Belum Upload SK
                    </span>
                </div>
            </div>

            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                <div class="flex gap-6 text-sm">
                    <div>
                        <span class="text-gray-500">Total Dosen:</span>
                        <span class="font-bold text-gray-800">{{ $totalDosen }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Total Mahasiswa:</span>
                        <span class="font-bold text-gray-800">{{ $totalMhs }}</span>
                    </div>
                </div>
            </div>

            {{-- FORM UPLOAD BARU --}}
            <div class="px-6 py-5">
                <form action="{{ route('baak.dosen-pembimbing.upload_sk.store', $prodi->id_prodi) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium mb-2">
                            Upload File SK Dosen Pembimbing
                        </label>
                        <input type="file"
                            name="file_sk"
                            accept=".pdf"
                            class="w-full border border-gray-300 text-sm text-gray-500
                                    file:mr-4
                                    file:py-2
                                    file:px-4
                                    file:border-0
                                    file:text-sm
                                    file:font-semibold
                                    file:bg-yellow-500
                                    file:text-white
                                    hover:file:bg-yellow-600"
                            required>
                        <p class="text-xs text-gray-400 mt-1">
                            Upload 1 file SK untuk seluruh dosen & mahasiswa di prodi ini. Format: PDF (Max 5MB)
                        </p>

                        @error('file_sk')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-3 items-center">
                        <button type="submit"
                                class="bg-yellow-500 text-white px-5 py-2 font-semibold hover:bg-yellow-600 transition">
                            📤 Upload SK
                        </button>
                    </div>
                </form>
            </div>

        @endif

    </div>

    {{-- ==================== DAFTAR DOSEN & MAHASISWA ==================== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="font-bold text-gray-800">📋 Daftar Dosen & Mahasiswa Pembimbing</h3>
            <p class="text-xs text-gray-500 mt-0.5">SK yang diupload akan berlaku untuk semua data di bawah ini</p>
        </div>

        @foreach($dospemGroups as $idDosen => $items)
        @php
            $dosen = $items->first()->dosen;
        @endphp
        <div class="px-6 py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-sm font-bold text-gray-800">{{ $loop->iteration }}. {{ $dosen->nama ?? '-' }}</span>
                <span class="text-xs text-gray-400">NIDN. {{ $dosen->nidn ?? '-' }}</span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $items->count() }} mhs</span>
            </div>
            <div class="ml-4">
                <table class="w-full text-sm">
                    <tbody>
                        @foreach($items as $idx => $item)
                        <tr class="border-b border-gray-50">
                            <td class="py-1 text-gray-400 w-8">{{ $idx + 1 }}</td>
                            <td class="py-1 text-gray-600 w-32">{{ $item->mahasiswa->npm ?? $item->mahasiswa->nim ?? '-' }}</td>
                            <td class="py-1 text-gray-800">{{ strtoupper($item->mahasiswa->nama ?? '-') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection