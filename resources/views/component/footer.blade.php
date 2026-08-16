<style>
/* ============================================
   FOOTER RESPONSIVE STYLES
   ============================================ */

/* Base Styles - Mobile First */
.footer {
    background: linear-gradient(to right, #fef08a, #fef9c3, #ffffff);
    margin-top: 2rem;
}

.footer-container {
    max-width: 80rem;
    margin-left: auto;
    margin-right: auto;
    padding: 1.25rem 1rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.footer-logo-wrapper {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
}

.footer-logo {
    width: 1.5rem;
    height: 1.5rem;
    display: block;
}

.footer-logo-text {
    font-weight: 700;
    font-size: 0.75rem;
    color: #1f2937;
}

.footer-desc {
    font-size: 0.625rem;
    color: #4b5563;
    line-height: 1.25;
}

.footer-heading {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.75rem;
    margin-bottom: 0.5rem;
}

.footer-text {
    font-size: 0.625rem;
    color: #4b5563;
}

/* Khusus No HP - lebih kecil di mobile */
.footer-phone {
    font-size: 0.5rem;
    color: #4b5563;
}

.footer-bottom {
    text-align: center;
    font-size: 0.625rem;
    color: #6b7280;
    padding: 0.5rem 0;
}

/* ============================================
   BREAKPOINT: Extra Small (max 374px)
   Untuk layar sangat kecil
   ============================================ */
@media (max-width: 374px) {
    .footer-container {
        padding-left: 0.625rem;
        padding-right: 0.625rem;
        gap: 0.5rem;
    }

    .footer-logo {
        width: 1.25rem;
        height: 1.25rem;
    }

    .footer-logo-text {
        font-size: 0.6875rem;
    }

    .footer-logo-wrapper {
        gap: 0.1875rem;
        margin-bottom: 0.375rem;
    }

    .footer-desc {
        font-size: 0.5625rem;
        line-height: 1.2;
    }

    .footer-heading {
        font-size: 0.6875rem;
        margin-bottom: 0.375rem;
    }

    .footer-text {
        font-size: 0.5625rem;
    }

    .footer-phone {
        font-size: 0.4375rem;
    }

    .footer-bottom {
        font-size: 0.5625rem;
        padding: 0.375rem 0;
    }
}

/* ============================================
   BREAKPOINT: Small (min 375px)
   Untuk smartphone standar
   ============================================ */
@media (min-width: 375px) {
    .footer {
        margin-top: 2.5rem;
    }
}

/* ============================================
   BREAKPOINT: Small to Medium (min 480px)
   Untuk smartphone besar / phablet
   ============================================ */
@media (min-width: 480px) {
    .footer-container {
        padding: 1.5rem 1.25rem;
        gap: 1rem;
    }

    .footer-logo {
        width: 1.75rem;
        height: 1.75rem;
    }

    .footer-logo-text {
        font-size: 0.8125rem;
    }

    .footer-logo-wrapper {
        gap: 0.3125rem;
        margin-bottom: 0.625rem;
    }

    .footer-desc {
        font-size: 0.6875rem;
        line-height: 1.3;
    }

    .footer-heading {
        font-size: 0.8125rem;
        margin-bottom: 0.625rem;
    }

    .footer-text {
        font-size: 0.6875rem;
    }

    .footer-phone {
        font-size: 0.5625rem;
    }

    .footer-bottom {
        font-size: 0.6875rem;
        padding: 0.625rem 0;
    }
}

/* ============================================
   BREAKPOINT: Medium (min 640px)
   Untuk tablet portrait
   ============================================ */
@media (min-width: 640px) {
    .footer {
        margin-top: 3.5rem;
    }

    .footer-container {
        padding: 2rem 1.5rem;
        gap: 1.5rem;
    }

    .footer-logo {
        width: 2.125rem;
        height: 2.125rem;
    }

    .footer-logo-text {
        font-size: 0.9375rem;
    }

    .footer-logo-wrapper {
        gap: 0.375rem;
        margin-bottom: 0.75rem;
    }

    .footer-desc {
        font-size: 0.8125rem;
        line-height: 1.375;
    }

    .footer-heading {
        font-size: 0.9375rem;
        margin-bottom: 0.75rem;
    }

    .footer-text {
        font-size: 0.8125rem;
    }

    .footer-phone {
        font-size: 0.75rem;
    }

    .footer-bottom {
        font-size: 0.8125rem;
        padding: 0.75rem 0;
    }
}

/* ============================================
   BREAKPOINT: Medium Large (min 768px)
   Untuk tablet landscape / laptop kecil
   ============================================ */
@media (min-width: 768px) {
    .footer {
        margin-top: 5rem;
    }

    .footer-container {
        padding: 3rem 2rem;
        gap: 2.5rem;
    }

    .footer-logo {
        width: 2.5rem;
        height: 2.5rem;
    }

    .footer-logo-text {
        font-size: 1.125rem;
    }

    .footer-logo-wrapper {
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .footer-desc {
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .footer-heading {
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .footer-text {
        font-size: 0.875rem;
    }

    /* No HP di desktop tetap 0.875rem, sama dengan footer-text */
    .footer-phone {
        font-size: 0.875rem;
    }

    .footer-bottom {
        font-size: 0.875rem;
        padding: 1rem 0;
    }
}

/* ============================================
   BREAKPOINT: Large (min 1024px)
   Untuk laptop / desktop standar
   ============================================ */
@media (min-width: 1024px) {
    .footer-container {
        gap: 3rem;
    }
}

/* ============================================
   BREAKPOINT: Extra Large (min 1280px)
   Untuk desktop besar
   ============================================ */
@media (min-width: 1280px) {
    .footer-container {
        gap: 4rem;
    }
}
</style>

<footer class="footer">
    <div class="footer-container">

        <!-- Logo + Deskripsi -->
        <div>
            <div class="footer-logo-wrapper">
                <img src="{{ asset('images/logo.png') }}" class="footer-logo">
                <h2 class="footer-logo-text">SIMMAGI</h2>
            </div>
            <p class="footer-desc">
                Sistem Informasi Monitoring Magang Industri untuk membantu
                pengelolaan dan pemantauan kegiatan magang mahasiswa secara
                efektif dan terintegrasi.
            </p>
        </div>

        <!-- Menu -->
        <div>
            
        </div>

        <!-- Kontak -->
        <div>
            <h3 class="footer-heading">Kontak</h3>
            <p class="footer-text"> admin@simmagi.com</p>
            <p class="footer-phone">+62 812 3456 7890</p>
            <p class="footer-text"> Cilacap, Jawa Tengah</p>
        </div>

    </div>

    <!-- Bottom -->
    <div class="footer-bottom">
        © {{ date('Y') }} SIMMAGI. All rights reserved.
    </div>
</footer>