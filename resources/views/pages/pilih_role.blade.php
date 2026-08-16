<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemilihan Role - SIMMAGI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #f8f7f4;
            --fg: #1a1a1a;
            --muted: #7a7670;
            --card: #ffffff;
            --border: #e8e5df;
            --accent-1: #c2780c;
            --accent-1-light: #fef7e8;
            --accent-1-mid: #f5dfa0;
            --accent-2: #0e7c6b;
            --accent-2-light: #eaf7f4;
            --accent-2-mid: #a3ddd3;
            --accent-3: #b5443a;
            --accent-3-light: #fdf0ee;
            --accent-3-mid: #f0b5b0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.03);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.06), 0 1px 4px rgba(0,0,0,0.04);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.10), 0 4px 12px rgba(0,0,0,0.05);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.13), 0 6px 20px rgba(0,0,0,0.06);
            --radius: 16px;
            --font: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        .role-page {
            font-family: var(--font);
            background-color: var(--bg);
            color: var(--fg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* ── Background dekorasi ── */
        .bg-decor {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .bg-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: blobFloat 20s ease-in-out infinite;
        }

        .bg-blob--1 {
            width: 400px; height: 400px;
            background: var(--accent-1-mid);
            top: -10%; left: -5%;
            animation-delay: 0s;
        }
        .bg-blob--2 {
            width: 350px; height: 350px;
            background: var(--accent-2-mid);
            bottom: -8%; right: -5%;
            animation-delay: -7s;
        }
        .bg-blob--3 {
            width: 280px; height: 280px;
            background: var(--accent-3-mid);
            top: 50%; left: 60%;
            transform: translate(-50%, -50%);
            animation-delay: -14s;
        }

        @keyframes blobFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -20px) scale(1.05); }
            50% { transform: translate(-20px, 15px) scale(0.95); }
            75% { transform: translate(15px, 25px) scale(1.03); }
        }

        /* Grid pattern overlay */
        .bg-grid {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background-image:
                linear-gradient(rgba(0,0,0,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.02) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* ── Konten utama ── */
        .role-page-inner {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 820px;
            padding: 24px 16px;
        }

        /* ── Header ── */
        .role-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 100px;
            background: var(--card);
            border: 1px solid var(--border);
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        .role-badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .role-heading {
            font-size: clamp(1.4rem, 4vw, 2rem);
            font-weight: 800;
            color: var(--fg);
            line-height: 1.2;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }

        .role-subheading {
            color: var(--muted);
            font-size: clamp(0.8rem, 2vw, 0.95rem);
            font-weight: 400;
            line-height: 1.6;
            max-width: 420px;
            margin: 0 auto;
        }

        /* ── Grid kartu ── */
        .role-grid {
            display: grid;
            gap: 16px;
            width: 100%;
        }
        .role-grid--cols-2 { grid-template-columns: repeat(2, 1fr); }
        .role-grid--cols-3 { grid-template-columns: repeat(3, 1fr); }

        @media (max-width: 640px) {
            .role-grid--cols-2,
            .role-grid--cols-3 { grid-template-columns: 1fr; }
        }

        /* ── Form wrapper ── */
        .role-form {
            display: block;
            text-decoration: none;
        }

        /* ── Kartu ── */
        .role-card {
            width: 100%;
            position: relative;
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 28px 20px 24px;
            text-align: center;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            transition:
                transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 0.35s cubic-bezier(0.22, 1, 0.36, 1),
                border-color 0.3s ease,
                background 0.3s ease;
            box-shadow: var(--shadow-sm);
            outline: none;
            font-family: var(--font);
            font-size: inherit;
            color: inherit;
            overflow: hidden;
            /* animasi masuk */
            opacity: 0;
            transform: translateY(24px);
            animation: cardEnter 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .role-card:nth-child(1) { animation-delay: 0.1s; }
        .role-card:nth-child(2) { animation-delay: 0.22s; }
        .role-card:nth-child(3) { animation-delay: 0.34s; }
        .role-card:nth-child(4) { animation-delay: 0.46s; }

        @keyframes cardEnter {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Shimmer line di atas card */
        .role-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: transparent;
            transition: background 0.3s ease;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        /* Hover glow di belakang */
        .role-card::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: var(--radius);
            opacity: 0;
            transition: opacity 0.35s ease;
            z-index: -1;
            filter: blur(12px);
        }

        .role-card:hover,
        .role-card:focus-visible {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
        }

        .role-card:active {
            transform: translateY(-2px) scale(0.985);
            box-shadow: var(--shadow-md);
            transition-duration: 0.12s;
        }

        /* ── Warna per kartu ── */
        .role-card--amber:hover { border-color: var(--accent-1); background: var(--accent-1-light); }
        .role-card--amber:hover::before { background: linear-gradient(90deg, var(--accent-1), #e5a528); }
        .role-card--amber:hover::after { background: var(--accent-1-mid); opacity: 0.5; }
        .role-card--amber:hover .role-initial { background: var(--accent-1); color: #fff; box-shadow: 0 4px 20px rgba(194,120,12,0.35); }
        .role-card--amber:hover .role-title { color: var(--accent-1); }
        .role-card--amber:hover .role-arrow { color: var(--accent-1); transform: translateX(3px); }

        .role-card--teal:hover { border-color: var(--accent-2); background: var(--accent-2-light); }
        .role-card--teal:hover::before { background: linear-gradient(90deg, var(--accent-2), #14b89e); }
        .role-card--teal:hover::after { background: var(--accent-2-mid); opacity: 0.5; }
        .role-card--teal:hover .role-initial { background: var(--accent-2); color: #fff; box-shadow: 0 4px 20px rgba(14,124,107,0.35); }
        .role-card--teal:hover .role-title { color: var(--accent-2); }
        .role-card--teal:hover .role-arrow { color: var(--accent-2); transform: translateX(3px); }

        .role-card--rose:hover { border-color: var(--accent-3); background: var(--accent-3-light); }
        .role-card--rose:hover::before { background: linear-gradient(90deg, var(--accent-3), #d4605a); }
        .role-card--rose:hover::after { background: var(--accent-3-mid); opacity: 0.5; }
        .role-card--rose:hover .role-initial { background: var(--accent-3); color: #fff; box-shadow: 0 4px 20px rgba(181,68,58,0.35); }
        .role-card--rose:hover .role-title { color: var(--accent-3); }
        .role-card--rose:hover .role-arrow { color: var(--accent-3); transform: translateX(3px); }

        /* ── Inisial (pengganti icon) ── */
        .role-initial {
            width: 56px; height: 56px;
            border-radius: 14px;
            background: #f3f1ec;
            color: var(--fg);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            flex-shrink: 0;
        }

        /* ── Teks ── */
        .role-text-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .role-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--fg);
            transition: color 0.3s ease;
            line-height: 1.3;
        }

        .role-desc {
            font-size: 0.75rem;
            color: var(--muted);
            font-weight: 400;
            line-height: 1.5;
        }

        /* ── Arrow indicator ── */
        .role-arrow {
            color: #c5c0b8;
            transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
            margin-top: 4px;
        }

        .role-arrow svg {
            width: 18px; height: 18px;
            display: block;
        }

        /* ── Footer ── */
        .role-footer {
            text-align: center;
            margin-top: 48px;
            font-size: 0.7rem;
            color: #b5b0a8;
            font-weight: 500;
            letter-spacing: 0.04em;
        }

        .role-footer span {
            font-weight: 700;
            color: #9a958e;
        }

        /* ── Ripple effect ── */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(0,0,0,0.06);
            transform: scale(0);
            animation: rippleOut 0.6s ease-out forwards;
            pointer-events: none;
        }

        @keyframes rippleOut {
            to { transform: scale(4); opacity: 0; }
        }

        /* ── Focus visible ── */
        .role-card:focus-visible {
            outline: 2px solid var(--accent-1);
            outline-offset: 3px;
        }

        /* ── Reduced motion ── */
        @media (prefers-reduced-motion: reduce) {
            .bg-blob { animation: none; }
            .role-card { animation: none; opacity: 1; transform: none; }
            .role-badge-dot { animation: none; }
            .role-card:hover { transform: none; }
            .ripple { animation: none; display: none; }
        }

        /* ── Tablet tweaks ── */
        @media (min-width: 641px) and (max-width: 1023px) {
            .role-grid--cols-3 { grid-template-columns: repeat(2, 1fr); }
            .role-grid--cols-3 .role-form:last-child {
                grid-column: 1 / -1;
                max-width: 360px;
                justify-self: center;
            }
        }
    </style>
</head>
<body class="role-page">

    <!-- Background dekorasi -->
    <div class="bg-decor" aria-hidden="true">
        <div class="bg-blob bg-blob--1"></div>
        <div class="bg-blob bg-blob--2"></div>
        <div class="bg-blob bg-blob--3"></div>
    </div>
    <div class="bg-grid" aria-hidden="true"></div>

    <div class="role-page-inner">

        <!-- Header -->
        <header class="role-header">
            <div class="role-badge">
                <span class="role-badge-dot"></span>
                SIMMAGI
            </div>
            <h1 class="role-heading">Pemilihan Role</h1>
            <p class="role-subheading">Pilih peran Anda untuk masuk ke halaman yang sesuai dengan fungsi dan tanggung jawab masing-masing.</p>
        </header>

        <!-- Grid kartu role -->
        <div class="role-grid role-grid--{{ count($roles) > 2 ? 'cols-3' : 'cols-2' }}">

            @foreach ($roles as $index => $role)
            <form class="role-form" action="{{ route($setRoleRoute) }}" method="POST">
                @csrf
                <input type="hidden" name="role_key" value="{{ $role['key'] }}">
                <input type="hidden" name="route" value="{{ $role['route'] }}">

                @php
                    // Mapping warna: amber, teal, rose secara berurutan
                    $colorMap = ['amber', 'teal', 'rose', 'amber', 'teal', 'rose'];
                    $colorClass = $colorMap[$index % 6];

                    // Ambil inisial dari label (max 2 huruf)
                    $words = preg_split('/[\s]+/', trim($role['label']));
                    $initial = '';
                    foreach ($words as $w) {
                        $initial .= strtoupper(mb_substr($w, 0, 1));
                        if (mb_strlen($initial) >= 2) break;
                    }
                @endphp

                <button type="submit" class="role-card role-card--{{ $colorClass }}"
                        aria-label="Masuk sebagai {{ $role['label'] }}"
                        onclick="createRipple(event)">

                    <div class="role-initial">{{ $initial }}</div>

                    <div class="role-text-wrap">
                        <h3 class="role-title">{{ $role['label'] }}</h3>
                        @if(isset($role['desc']))
                            <p class="role-desc">{{ $role['desc'] }}</p>
                        @endif
                    </div>

                    <span class="role-arrow" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                </button>
            </form>
            @endforeach

        </div>

        <!-- Footer -->
        <footer class="role-footer">
            <span>SIMMAGI</span> &mdash; Sistem Informasi Manajemen Magang
        </footer>

    </div>

    <script>
        /**
         * Efek ripple saat klik kartu
         */
        function createRipple(e) {
            const card = e.currentTarget;
            const rect = card.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';

            card.appendChild(ripple);

            // Hapus elemen ripple setelah animasi selesai
            ripple.addEventListener('animationend', function () {
                ripple.remove();
            });
        }

        /**
         * Parallax halus pada blob background saat gerak mouse
         * Hanya aktif di layar besar
         */
        if (window.matchMedia('(min-width: 768px)').matches) {
            const blobs = document.querySelectorAll('.bg-blob');
            let rafId = null;

            document.addEventListener('mousemove', function (e) {
                if (rafId) return;
                rafId = requestAnimationFrame(function () {
                    const cx = (e.clientX / window.innerWidth - 0.5) * 2;  // -1 s/d 1
                    const cy = (e.clientY / window.innerHeight - 0.5) * 2;

                    blobs[0].style.transform = 'translate(' + (cx * 20) + 'px, ' + (cy * 15) + 'px)';
                    blobs[1].style.transform = 'translate(' + (cx * -15) + 'px, ' + (cy * -20) + 'px)';
                    blobs[2].style.transform = 'translate(' + (cx * 10) + 'px, ' + (cy * -10) + 'px)';

                    rafId = null;
                });
            });
        }
    </script>

</body>
</html>