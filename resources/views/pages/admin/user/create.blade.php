<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Tambah User</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-10">

    <div class="w-full max-w-2xl">

        <div class="bg-white shadow-lg p-10 rounded-lg border border-gray-200">
            <h2 class="text-center text-3xl font-bold text-gray-800 mb-8">.:: Tambah User ::.</h2>
            <form action="{{ route('admin.user.store') }}" method="POST" id="formUser" autocomplete="off">
                @csrf

                <div class="space-y-6">

                    {{-- Username --}}
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-user"></i>
                            </span>
                            <!-- Tambah autocomplete="off" -->
                            <input type="text" name="username" id="username" value="{{ old('username') }}" 
                                autocomplete="off"
                                class="w-full border border-gray-300 rounded-lg p-3 pl-10 focus:ring-orange-500 focus:border-orange-500 @error('username') border-red-500 @enderror"
                                placeholder="Masukkan username" autofocus>
                        </div>
                        @error('username')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <!-- Tambah autocomplete="new-password" agar browser tidak isikan password lama -->
                            <input type="password" name="password" id="password" 
                                autocomplete="new-password"
                                class="w-full border border-gray-300 rounded-lg p-3 pl-10 pr-12 focus:ring-orange-500 focus:border-orange-500 @error('password') border-red-500 @enderror"
                                placeholder="Minimal 6 karakter">
                            <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        {{-- Indikator kekuatan password --}}
                        <div class="mt-2 flex gap-1">
                            <div id="str1" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all duration-300"></div>
                            <div id="str2" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all duration-300"></div>
                            <div id="str3" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all duration-300"></div>
                            <div id="str4" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all duration-300"></div>
                        </div>
                        <p id="strText" class="text-xs mt-1 text-gray-400"></p>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <!-- Tambah autocomplete="new-password" -->
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                autocomplete="new-password"
                                class="w-full border border-gray-300 rounded-lg p-3 pl-10 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Ulangi password">
                        </div>
                        <p id="matchText" class="text-xs mt-1 text-gray-400"></p>
                    </div>

                    {{-- Role --}}
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                        <select name="role" id="role" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-orange-500 focus:border-orange-500 @error('role') border-red-500 @enderror">
                            <option value="">-- Pilih Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r }}" {{ old('role') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CHECKBOX DIHAPUS, GANTI INFO OTOMATIS --}}
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-green-200">
                        <i class="fas fa-shield-alt text-green-600"></i>
                        <p class="text-sm text-green-700">
                            <strong>Wajib Ganti Password:</strong> Akun baru akan otomatis diminta mengganti kata sandi saat pertama kali login.
                        </p>
                    </div>
                </div>

                {{-- Info --}}
                <div class="mt-8 p-4 bg-[#fffbeb] border border-[#ece75f]/40 rounded-lg">
                    <p class="text-sm text-[#b45309]">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Catatan:</strong> 
                        <ul class="list-disc list-inside mt-1 space-y-0.5">
                            <li>User dengan role <strong>Mahasiswa/Dosen/Pegawai</strong> sebaiknya dibuat otomatis melalui menu masing-masing.</li>
                            <li>Menu ini cocok untuk membuat akun <strong>Admin</strong> baru atau akun khusus.</li>
                        </ul>
                    </p>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-between mt-8">
                    <a href="{{ route('admin.user.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <button type="submit" id="btnSubmit" class="bg-orange-500 text-white px-8 py-2.5 rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">SIMMAGI - Sistem Informasi Monitoring Magang Industri</p>
    </div>

<script>
    // Toggle show/hide password
    document.getElementById('togglePassword').addEventListener('click', function() {
        const pwInput = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (pwInput.type === 'password') {
            pwInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pwInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Indikator kekuatan password
    document.getElementById('password').addEventListener('input', function() {
        const val = this.value;
        let strength = 0;
        if (val.length >= 6) strength++;
        if (val.length >= 10) strength++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) strength++;
        if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) strength++;

        const colors = ['#ef4444', '#f59e0b', '#3b82f6', '#22c55e'];
        const labels = ['Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];

        for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById('str' + i);
            if (i <= strength) {
                bar.style.backgroundColor = colors[strength - 1];
            } else {
                bar.style.backgroundColor = '#e5e7eb';
            }
        }

        const textEl = document.getElementById('strText');
        if (val.length === 0) {
            textEl.textContent = '';
            textEl.className = 'text-xs mt-1 text-gray-400';
        } else {
            textEl.textContent = 'Kekuatan: ' + (labels[strength - 1] || 'Terlalu pendek');
            textEl.style.color = colors[strength - 1] || '#ef4444';
        }

        checkMatch();
    });

    // Cek kecocokan password
    document.getElementById('password_confirmation').addEventListener('input', checkMatch);

    function checkMatch() {
        const pw = document.getElementById('password').value;
        const cf = document.getElementById('password_confirmation').value;
        const el = document.getElementById('matchText');
        const btn = document.getElementById('btnSubmit');

        if (cf.length === 0) {
            el.textContent = '';
            btn.disabled = false;
            return;
        }

        if (pw === cf) {
            el.textContent = 'Password cocok';
            el.style.color = '#22c55e';
            btn.disabled = false;
        } else {
            el.textContent = 'Password tidak cocok';
            el.style.color = '#ef4444';
            btn.disabled = true;
        }
    }

    // Block submit jika password tidak cocok
    document.getElementById('formUser').addEventListener('submit', function(e) {
        const pw = document.getElementById('password').value;
        const cf = document.getElementById('password_confirmation').value;
        if (pw !== cf) {
            e.preventDefault();
            document.getElementById('matchText').textContent = 'Password tidak cocok!';
            document.getElementById('matchText').style.color = '#ef4444';
        }
    });
</script>
</body>
</html>