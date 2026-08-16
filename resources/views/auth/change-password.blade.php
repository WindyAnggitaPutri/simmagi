<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Kata Sandi - SIMMAGI</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md">

        <!-- Tombol Kembali ke Login -->
        <div class="mb-4">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-amber-600 transition font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Login
            </a>
        </div>

        <!-- Card Ganti Password -->
        <div class="bg-white border border-amber-100 rounded-lg shadow-sm p-8">
            
            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Ganti Kata Sandi</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Ini adalah login pertama Anda. Harap ubah kata sandi default Anda untuk keamanan akun.
                </p>
            </div>

            {{-- Error Message --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm p-3 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('password.change.update') }}" method="POST" class="space-y-5">
                @csrf
                
                {{-- Input Kata Sandi Baru --}}
                <div>
                    <label class="text-sm font-medium text-gray-600">Kata Sandi Baru</label>
                    <div class="relative mt-1">
                        <input id="new-password-input" name="password" type="password" required
                            class="w-full px-4 py-2 pr-10 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
                            placeholder="Minimal 6 karakter">
                        
                        <button type="button" id="toggle-new-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition z-10">
                            <svg id="icon-new-eye" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="icon-new-eye-off" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Input Konfirmasi Kata Sandi --}}
                <div>
                    <label class="text-sm font-medium text-gray-600">Konfirmasi Kata Sandi</label>
                    <div class="relative mt-1">
                        <input id="confirm-password-input" name="password_confirmation" type="password" required
                            class="w-full px-4 py-2 pr-10 border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
                            placeholder="Ulangi kata sandi">
                        
                        <button type="button" id="toggle-confirm-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition z-10">
                            <svg id="icon-confirm-eye" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="icon-confirm-eye-off" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Button Submit -->
                <button 
                    type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 rounded transition shadow-sm"
                >
                    Simpan Kata Sandi Baru
                </button>

            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">
            © {{ date('Y') }} SIMMAGI
        </p>

    </div>

</body>
</html>

<script>
    // Toggle untuk Kata Sandi Baru
    document.getElementById('toggle-new-password').addEventListener('click', function() {
        const passwordInput = document.getElementById('new-password-input');
        const iconEye = document.getElementById('icon-new-eye');
        const iconEyeOff = document.getElementById('icon-new-eye-off');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            iconEye.classList.add('hidden');
            iconEyeOff.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            iconEye.classList.remove('hidden');
            iconEyeOff.classList.add('hidden');
        }
    });

    // Toggle untuk Konfirmasi Kata Sandi
    document.getElementById('toggle-confirm-password').addEventListener('click', function() {
        const passwordInput = document.getElementById('confirm-password-input');
        const iconEye = document.getElementById('icon-confirm-eye');
        const iconEyeOff = document.getElementById('icon-confirm-eye-off');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            iconEye.classList.add('hidden');
            iconEyeOff.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            iconEye.classList.remove('hidden');
            iconEyeOff.classList.add('hidden');
        }
    });
</script>