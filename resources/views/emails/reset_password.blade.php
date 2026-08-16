<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SIMMAGI- Politeknik Negeri Cilacap</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f9fafb; font-family: Arial, sans-serif;">

    <div style="max-width: 560px; margin: 40px auto; background-color: #ffffff; border: 1px solid #fef3c7; border-radius: 8px; overflow: hidden;">

        <!-- Header -->
        <div style="background-color: #f59e0b; padding: 24px 32px; text-align: center;">
            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;">SIMMAGI- Politeknik Negeri Cilacap</h1>
            <p style="margin: 4px 0 0; color: #fef3c7; font-size: 13px;">Sistem Monitoring Magang</p>
        </div>

        <!-- Body -->
        <div style="padding: 32px;">

            <p style="margin: 0 0 16px; font-size: 15px; color: #374151;">
                Halo <strong>{{ $nama }}</strong>,
            </p>

            <p style="margin: 0 0 16px; font-size: 15px; color: #374151;">
                Kami menerima permintaan untuk mereset password akun Anda. Klik tombol di bawah ini untuk membuat password baru:
            </p>

            <!-- Tombol -->
            <div style="text-align: center; margin: 28px 0;">
                <a href="{{ $resetUrl }}" 
                   style="display: inline-block; background-color: #f59e0b; color: #ffffff; font-size: 15px; font-weight: bold; padding: 12px 32px; border-radius: 6px; text-decoration: none;">
                    Reset Password
                </a>
            </div>

            <p style="margin: 0 0 12px; font-size: 13px; color: #6b7280;">
                Jika tombol di atas tidak berfungsi, salin dan tempel link berikut di browser Anda:
            </p>
            <p style="margin: 0 0 20px; font-size: 13px; color: #d97706; word-break: break-all;">
                {{ $resetUrl }}
            </p>

            <!-- Info Expired -->
            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 12px 16px; margin: 0 0 20px;">
                <p style="margin: 0; font-size: 13px; color: #92400e;">
                    ⏰ Link ini berlaku selama <strong>60 menit</strong> sejak dikirim. Jika sudah kedaluwarsa, silakan ajukan permintaan reset password baru.
                </p>
            </div>

            <p style="margin: 0 0 8px; font-size: 13px; color: #6b7280;">
                Jika Anda tidak meminta reset password, Anda bisa mengabaikan email ini. Password Anda tidak akan berubah.
            </p>

        </div>

        <!-- Footer -->
        <div style="background-color: #f9fafb; padding: 20px 32px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                 {{ date('Y') }} SIMMAGI - Sistem Monitoring Magang - Politkenik Negeri Cilacap
            </p>
            <p style="margin: 4px 0 0; font-size: 11px; color: #d1d5db;">
                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
            </p>
        </div>

    </div>

</body>
</html>