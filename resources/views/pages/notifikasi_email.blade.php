<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f5; font-family: sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; padding: 24px;">

        <!-- HEADER -->
        <div style="background: #F59E0B; border-radius: 16px 16px 0 0; padding: 32px; text-align: center;">
            <h1 style="color: white; font-size: 22px; margin: 0;"> SIMMAGI</h1>
            <p style="color: white; font-size: 12px; margin: 6px 0 0 0;">Sistem Informasi Manajemen Magang</p>
        </div>

        <!-- BODY -->
        <div style="background: white; padding: 40px; border: 1px solid #e5e7eb;">

            @if($namaPenerima)
            <p style="font-size: 16px; color: #374151; margin-top: 0;">
                Halo, <strong>{{ $namaPenerima }}</strong> 
            </p>
            @endif

            <!-- BADGE -->
            <div style="margin-bottom: 16px;">
                <span style="display: inline-block; background: {{ $badgeColor }}1A; color: {{ $badgeColor }}; font-size: 11px; font-weight: 700; padding: 4px 14px; border-radius: 20px; letter-spacing: 0.5px;">
                    {{ $kategori }}
                </span>
            </div>

            <h2 style="font-size: 20px; color: #111827; margin-top: 8px; margin-bottom: 16px;">{{ $judul }}</h2>

            <div style="background: #F9FAFB; border-radius: 12px; padding: 20px; border-left: 4px solid {{ $badgeColor }};">
                <p style="font-size: 15px; line-height: 1.7; color: #374151; margin: 0;">
                    {{ $pesan }}
                </p>
            </div>

           
        </div>

        <!-- FOOTER -->
        <div style="background: #F59E0B; border-radius: 0 0 16px 16px; padding: 24px; text-align: center;">
            <p style="color: white; font-size: 12px; margin: 0;">
                 {{ date('Y') }} SIMMAGI — Email ini dikirim otomatis oleh sistem.
            </p>
        </div>
    </div>
</body>
</html>