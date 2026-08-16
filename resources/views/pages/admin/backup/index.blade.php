@extends('layouts.admin')

@section('title', 'SIMMAGI - Backup Sistem')
@section('page-title', 'Backup Sistem')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Backup Sistem</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola backup database dan source code aplikasi SIMMAGI</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
            <button @click="show = false" class="ml-auto text-green-400 hover:text-green-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-red-700">{{ session('error') }}</p>
            <button @click="show = false" class="ml-auto text-red-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <!-- {{-- Info Database Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Jumlah Tabel</p>
                <p class="text-xl font-bold text-gray-800">{{ $tableCount }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Ukuran Database</p>
                <p class="text-xl font-bold text-gray-800">{{ number_format($dbSize / 1048576, 2) }} MB</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Backup DB Terakhir</p>
                <p class="text-sm font-semibold text-gray-800">{{ $lastDbBackup ? $lastDbBackup['created_at'] : 'Belum ada' }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Backup Code Terakhir</p>
                <p class="text-sm font-semibold text-gray-800">{{ $lastCodeBackup ? $lastCodeBackup['created_at'] : 'Belum ada' }}</p>
            </div>
        </div>
    </div> -->

    {{-- Action Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Card: Backup Database --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <!-- <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg> -->
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800">Backup Database</h2>
                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">
                        Mengunduh seluruh data dari <strong>{{ $tableCount }} tabel</strong> dalam format 
                        <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">.sql</code>. 
                        File bisa di-restore langsung via phpMyAdmin atau MySQL CLI.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.backup.database') }}" id="btn-backup-db"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition active:scale-95">
                            <!-- <svg id="icon-spinner-db" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg id="icon-download-db" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg> -->
                            <span id="text-btn-db">Download Database</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Backup Source Code --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <!-- <svg class="w-7 h-7 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg> -->
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800">Backup Source Code</h2>
                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">
                        Mengunduh seluruh source code dalam format 
                        <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">.zip</code>. 
                        Folder <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">vendor</code>, 
                        <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">node_modules</code>, dan 
                        <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">.git</code> tidak disertakan.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.backup.source-code') }}" id="btn-backup-code"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition active:scale-95">
                            <svg id="icon-spinner-code" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <!-- <svg id="icon-download-code" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg> -->
                            <span id="text-btn-code">Download Code</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

 

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btnDb = document.getElementById('btn-backup-db');
        if (btnDb) {
            btnDb.addEventListener('click', function () {
                document.getElementById('icon-download-db').classList.add('hidden');
                document.getElementById('icon-spinner-db').classList.remove('hidden');
                document.getElementById('text-btn-db').textContent = 'Memproses...';
            });
        }

        var btnCode = document.getElementById('btn-backup-code');
        if (btnCode) {
            btnCode.addEventListener('click', function () {
                document.getElementById('icon-download-code').classList.add('hidden');
                document.getElementById('icon-spinner-code').classList.remove('hidden');
                document.getElementById('text-btn-code').textContent = 'Memproses...';
            });
        }

        document.getElementById('icon-download-db')?.classList.remove('hidden');
        document.getElementById('icon-spinner-db')?.classList.add('hidden');
        document.getElementById('text-btn-db').textContent = 'Download Database';
        document.getElementById('icon-download-code')?.classList.remove('hidden');
        document.getElementById('icon-spinner-code')?.classList.add('hidden');
        document.getElementById('text-btn-code').textContent = 'Download Code';
    });
</script>
@endpush

@endsection