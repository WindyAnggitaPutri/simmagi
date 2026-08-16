<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333; max-width: 650px; margin: 0 auto; padding: 20px;">
    
    <div style="border-bottom: 3px solid #1e40af; padding-bottom: 15px; margin-bottom: 25px;">
        <h2 style="color: #1e40af; margin: 0; font-size: 18px;">
             JURUSAN KOMPUTER DAN BISNIS
        </h2>
        <p style="color: #666; margin: 5px 0 0 0; font-size: 12px;">
            Surat {{ $jenisSurat === 'permohonan' ? 'Permohonan' : 'Pengantar' }} Magang Industri
        </p>
    </div>

    <p>Yth. <strong>{{ $ditujukanKepada }}</strong><br>
    <strong>{{ $namaPerusahaan }}</strong></p>

    <p>Dengan hormat,</p>

    @if($jenisSurat === 'permohonan')
        <p>Bersama email ini, kami sampaikan Surat Permohonan Magang dari Jurusan Komputer dan Bisnis untuk mahasiswa berikut:</p>
    @else
        <p>Bersama email ini, kami sampaikan Surat Pengantar Magang dari Jurusan Komputer dan Bisnis untuk mahasiswa berikut:</p>
    @endif

    <div style="background: #f0f4ff; border-left: 4px solid #1e40af; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
        <table style="width: 100%; font-size: 14px;">
            <tr>
                <td style="width: 160px; padding: 4px 0; color: #666;">Nama Mahasiswa</td>
                <td style="padding: 4px 0;"><strong>{{ $namaMahasiswa }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 4px 0; color: #666;">Periode Magang</td>
                <td style="padding: 4px 0;"><strong>{{ $periode }}</strong></td>
            </tr>
        </table>
    </div>

    <p>Surat resmi terlampir dalam format PDF. Mohon dapat ditindaklanjuti sesuai prosedur perusahaan.</p>

    <p>Apabila ada pertanyaan lebih lanjut, silakan hubungi kami melalui email balasan ini.</p>

    <p style="margin-top: 30px;">
        Hormat kami,<br><br>
        <strong>Bagian Akademik dan Kemahasiswaan</strong><br>
        <span style="color: #666; font-size: 12px;">Jurus Komputer dan Bisnis</span>
    </p>

    <div style="border-top: 1px solid #ddd; margin-top: 30px; padding-top: 15px; font-size: 11px; color: #999; text-align: center;">
        Email ini dikirim otomatis oleh SIMMAGI (Sistem Informasi Manajemen Magang Industri)
    </div>

</body>
</html>