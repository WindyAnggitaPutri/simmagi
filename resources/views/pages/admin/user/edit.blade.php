<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMMAGI - Edit User</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-10">

    <div class="w-full max-w-2xl">

        <div class="bg-white shadow-lg p-10 rounded-lg border border-gray-200">
            <h2 class="text-center text-3xl font-bold text-gray-800 mb-8">.:: Edit User ::.</h2>
            <form action="{{ route('admin.user.update', $user->id_user) }}" method="POST" id="formUser">
                @csrf
                @method('PUT')

                <div class="space-y-6">

                    {{-- Username --}}
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" 
                                class="w-full border border-gray-300 rounded-lg p-3 pl-10 focus:ring-orange-500 focus:border-orange-500 @error('username') border-red-500 @enderror"
                                placeholder="Masukkan username">
                        </div>
                        @error('username')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password (Opsional saat edit) --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password Baru <span class="text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah)</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" id="password" disable
                                class="w-full border border-gray-300 rounded-lg p-3 pl-10 pr-12 focus:ring-orange-500 focus:border-orange-500 @error('password') border-red-500 @enderror"
                                placeholder="Minimal 6 karakter">
                            <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                class="w-full border border-gray-300 rounded-lg p-3 pl-10 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Ulangi password baru">
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
                                <option value="{{ $r }}" {{ old('role', $user->role) == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ✅ INFO STATUS GANTI PASSWORD (Bukan input) --}}
                    <div class="flex items-center gap-3 p-3 {{ $user->must_change_password ? 'bg-amber-50 border-amber-200' : 'bg-green-50 border-green-200' }} rounded-lg border">
                        @if($user->must_change_password)
                            <i class="fas fa-exclamation-triangle text-amber-600"></i>
                            <p class="text-sm text-amber-700">
                                <strong>Status:</strong> User ini <strong>wajib mengganti password</strong> saat login berikutnya.
                            </p>
                        @else
                            <i class="fas fa-check-circle text-green-600"></i>
                            <p class="text-sm text-green-700">
                                <strong>Status:</strong> User ini sudah mengganti password default-nya.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-between mt-8">
                    <a href="{{ route('admin.user.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <button type="submit" id="btnSubmit" class="bg-orange-500 text-white px-8 py-2.5 rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
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

    // Cek kecocokan password (hanya jika diisi)
    document.getElementById('password_confirmation').addEventListener('input', checkMatch);
    document.getElementById('password').addEventListener('input', checkMatch);

    function checkMatch() {
        const pw = document.getElementById('password').value;
        const cf = document.getElementById('password_confirmation').value;
        const el = document.getElementById('matchText');
        const btn = document.getElementById('btnSubmit');

        // Jika kedua field kosong, tidak perlu cek
        if (pw.length === 0 && cf.length === 0) {
            el.textContent = '';
            btn.disabled = false;
            return;
        }

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
        // Hanya validasi jika password diisi
        if (pw.length > 0 && pw !== cf) {
            e.preventDefault();
            document.getElementById('matchText').textContent = 'Password tidak cocok!';
            document.getElementById('matchText').style.color = '#ef4444';
        }
    });
</script>
</body>
</html>