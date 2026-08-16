<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Hitungin - Aplikasi Pencatatan Keuangan Pribadi</title>
    <meta name="description" content="Hitungin membantu kamu mencatat pemasukan dan pengeluaran secara otomatis dari notifikasi bank dan e-wallet. Gratis, mudah, dan akurat.">
    <meta name="keywords" content="pencatatan keuangan, aplikasi keuangan, catatan pengeluaran, catatan pemasukan, budget tracker, keuangan pribadi, bank, e-wallet, otomatis">
    <meta name="author" content="Hitungin">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://hitungin.com/">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://hitungin.com/">
    <meta property="og:title" content="Hitungin - Aplikasi Pencatatan Keuangan Pribadi">
    <meta property="og:description" content="Hitungin membantu kamu mencatat pemasukan dan pengeluaran secara otomatis dari notifikasi bank dan e-wallet.">
    <meta property="og:image" content="https://hitungin.com/icons/og-image.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://hitungin.com/">
    <meta property="twitter:title" content="Hitungin - Aplikasi Pencatatan Keuangan Pribadi">
    <meta property="twitter:description" content="Hitungin membantu kamu mencatat pemasukan dan pengeluaran secara otomatis dari notifikasi bank dan e-wallet.">
    <meta property="twitter:image" content="https://hitungin.com/icons/og-image.png">

    <!-- Theme Color -->
    <meta name="theme-color" content="#4F46E5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Structured Data -->
    @verbatim
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "MobileApplication",
        "name": "Hitungin",
        "operatingSystem": "Android",
        "applicationCategory": "FinanceApplication",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR"
        },
        "description": "Aplikasi pencatatan keuangan pribadi dengan deteksi otomatis dari notifikasi bank dan e-wallet.",
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "150"
        }
    }
    </script>
    @endverbatim

    @verbatim
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #FAFAFA;
            color: #1a1a2e;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e5e7eb;
            z-index: 100;
        }
        nav .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 800;
            color: #4F46E5;
            text-decoration: none;
        }
        .logo-icon {
            width: 36px;
            height: 36px;
            background: #4F46E5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        .nav-links {
            display: flex;
            gap: 32px;
            list-style: none;
        }
        .nav-links a {
            text-decoration: none;
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: #4F46E5; }
        .nav-cta {
            background: #4F46E5;
            color: white !important;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600 !important;
        }
        .nav-cta:hover { background: #4338CA; }

        /* Hero */
        .hero {
            padding: 140px 0 80px;
            text-align: center;
            background: linear-gradient(180deg, #EEF2FF 0%, #FAFAFA 100%);
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            color: #4F46E5;
            border: 1px solid #E0E7FF;
            margin-bottom: 24px;
        }
        .hero h1 {
            font-size: clamp(36px, 6vw, 64px);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 20px;
            color: #0f172a;
        }
        .hero h1 span { color: #4F46E5; }
        .hero p {
            font-size: clamp(16px, 2vw, 20px);
            color: #64748b;
            max-width: 600px;
            margin: 0 auto 40px;
        }
        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #4F46E5;
            color: white;
            padding: 16px 32px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }
        .btn-download:hover {
            background: #4338CA;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
        }
        .btn-download svg {
            width: 24px;
            height: 24px;
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #374151;
            padding: 16px 32px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border: 2px solid #e5e7eb;
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            border-color: #4F46E5;
            color: #4F46E5;
        }

        /* Hero Image */
        .hero-image {
            margin-top: 60px;
            position: relative;
        }
        .hero-image img {
            max-width: 400px;
            width: 100%;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        .hero-float {
            position: absolute;
            background: white;
            padding: 12px 16px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            font-size: 13px;
            font-weight: 600;
            animation: float 3s ease-in-out infinite;
        }
        .hero-float-1 { top: 20%; left: 5%; animation-delay: 0s; }
        .hero-float-2 { top: 40%; right: 5%; animation-delay: 1s; }
        .hero-float-3 { bottom: 20%; left: 10%; animation-delay: 2s; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Features */
        .features {
            padding: 100px 0;
        }
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        .section-title h2 {
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 800;
            margin-bottom: 16px;
            color: #0f172a;
        }
        .section-title p {
            font-size: 18px;
            color: #64748b;
            max-width: 500px;
            margin: 0 auto;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }
        .feature-card {
            background: white;
            padding: 32px;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            transition: all 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
            border-color: #E0E7FF;
        }
        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        .feature-card h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #0f172a;
        }
        .feature-card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
        }

        /* How it Works */
        .how-it-works {
            padding: 100px 0;
            background: #F8FAFC;
        }
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 32px;
            counter-reset: step;
        }
        .step {
            text-align: center;
            position: relative;
        }
        .step-number {
            width: 64px;
            height: 64px;
            background: #EEF2FF;
            color: #4F46E5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            margin: 0 auto 20px;
        }
        .step h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .step p {
            font-size: 14px;
            color: #64748b;
            max-width: 280px;
            margin: 0 auto;
        }

        /* Stats */
        .stats {
            padding: 80px 0;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }
        .stat h3 {
            font-size: clamp(36px, 5vw, 52px);
            font-weight: 900;
        }
        .stat p {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 8px;
        }

        /* FAQ */
        .faq {
            padding: 100px 0;
        }
        .faq-list {
            max-width: 700px;
            margin: 0 auto;
        }
        .faq-item {
            border-bottom: 1px solid #e5e7eb;
            padding: 24px 0;
        }
        .faq-item h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #0f172a;
        }
        .faq-item p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
        }

        /* CTA */
        .cta {
            padding: 100px 0;
            text-align: center;
            background: #EEF2FF;
        }
        .cta h2 {
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 800;
            margin-bottom: 16px;
        }
        .cta p {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 40px;
        }

        /* Footer */
        footer {
            padding: 48px 0;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        footer p {
            font-size: 14px;
            color: #94a3b8;
        }
        footer a {
            color: #4F46E5;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero-float { display: none; }
            .hero { padding: 120px 0 60px; }
        }
    </style>
    @endverbatim
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="container">
            <a href="/" class="logo">
                <div class="logo-icon">H</div>
                Hitungin
            </a>
            <ul class="nav-links">
                <li><a href="#fitur">Fitur</a></li>
                <li><a href="#cara-kerja">Cara Kerja</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#download" class="nav-cta">Download</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero" id="download">
        <div class="container">
            <div class="hero-badge">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                v1.0.0 sudah rilis!
            </div>
            <h1>Catat Keuangan<br><span>Otomatis</span> dari Notifikasi HP</h1>
            <p>Hitungin membantu kamu mencatat pemasukan dan pengeluaran secara otomatis dari notifikasi bank dan e-wallet. Tanpa perlu input manual.</p>
            <div class="hero-buttons">
                <a href="https://github.com/arkastudio462/hitungin/releases/latest" class="btn-download" target="_blank" rel="noopener">
                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.98-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                    Download APK
                </a>
                <a href="https://github.com/arkastudio462/hitungin" class="btn-secondary" target="_blank" rel="noopener">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>
                    Lihat Source Code
                </a>
            </div>

            <!-- Phone Mockup -->
            <div class="hero-image">
                <div style="background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); max-width: 320px; height: 560px; margin: 0 auto; border-radius: 40px; padding: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                    <div style="background: #fff; border-radius: 32px; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #4F46E5;">
                        <svg width="80" height="80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" /></svg>
                        <p style="font-weight: 800; font-size: 18px; margin-top: 12px;">Hitungin</p>
                        <p style="font-size: 12px; color: #94a3b8;">Pencatatan Otomatis</p>
                    </div>
                </div>
                <div class="hero-float hero-float-1">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #10b981; font-size: 20px;">+</span>
                        <div>
                            <div style="font-size: 11px; color: #94a3b8;">Gaji Masuk</div>
                            <div style="color: #10b981; font-weight: 800;">+Rp 8.500.000</div>
                        </div>
                    </div>
                </div>
                <div class="hero-float hero-float-2">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #ef4444; font-size: 20px;">-</span>
                        <div>
                            <div style="font-size: 11px; color: #94a3b8;">Beli Makan</div>
                            <div style="color: #ef4444; font-weight: 800;">-Rp 45.000</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="fitur">
        <div class="container">
            <div class="section-title">
                <h2>Kenapa Pilih Hitungin?</h2>
                <p>Fitur lengkap untuk mengelola keuangan pribadi kamu</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #EEF2FF; color: #4F46E5;">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    </div>
                    <h3>Otomatis dari Notifikasi</h3>
                    <p>Tidak perlu input manual. Hitungin membaca notifikasi bank dan e-wallet lalu mencatat transaksi secara otomatis.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #FEF3C7; color: #F59E0B;">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h3>AI Parsing Cerdas</h3>
                    <p>Menggunakan AI untuk memahami notifikasi dari berbagai bank dan e-wallet dengan akurasi tinggi.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #DCFCE7; color: #22C55E;">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </div>
                    <h3>Dashboard Visual</h3>
                    <p>Lihat grafik pemasukan dan pengeluaran kamu secara real-time. Mudah dipahami dan menarik.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #FCE7F3; color: #EC4899;">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3>Budget & Anggaran</h3>
                    <p>Atur anggaran bulanan per kategori. Dapat notifikasi jika pengeluaran mendekati batas.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #E0E7FF; color: #6366F1;">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <h3>Aman & Privat</h3>
                    <p>Data keuangan kamu tersimpan aman. Tidak ada data yang dibagikan ke pihak ketiga.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #FEE2E2; color: #EF4444;">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                    </div>
                    <h3>Gratis Selamanya</h3>
                    <p>Semua fitur tersedia gratis. Tanpa biaya tersembunyi, tanpa iklan mengganggu.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="how-it-works" id="cara-kerja">
        <div class="container">
            <div class="section-title">
                <h2>Cara Kerja</h2>
                <p>Mulai catat keuangan dalam 3 langkah mudah</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Download & Install</h3>
                    <p>Download APK Hitungin dan install di HP Android kamu. Gratis tanpa ribet.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Hubungkan Akun</h3>
                    <p>Login dengan akun Hitungin kamu. Aktifkan akses notifikasi untuk bank & e-wallet.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Otomatis Mencatat</h3>
                    <p>Setiap notifikasi transaksi dari bank/e-wallet akan otomatis tercatat sebagai transaksi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat">
                    <h3>10+</h3>
                    <p>Bank & E-Wallet</p>
                </div>
                <div class="stat">
                    <h3>100%</h3>
                    <p>Gratis</p>
                </div>
                <div class="stat">
                    <h3>Otomatis</h3>
                    <p>Tanpa Input Manual</p>
                </div>
                <div class="stat">
                    <h3>AI</h3>
                    <p>Parsing Cerdas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq" id="faq">
        <div class="container">
            <div class="section-title">
                <h2>Pertanyaan Umum</h2>
            </div>
            <div class="faq-list">
                <div class="faq-item">
                    <h3>Bank dan e-wallet apa saja yang didukung?</h3>
                    <p>Hitungin mendukung BCA, Mandiri, BRI, BNI, CIMB, BSI, Jenius, GoPay, OVO, DANA, ShopeePay, dan LinkAja. Kami terus menambah dukungan bank lainnya.</p>
                </div>
                <div class="faq-item">
                    <h3>Apakah data keuangan saya aman?</h3>
                    <p>Ya, data kamu tersimpan di server yang aman dan tidak dibagikan ke pihak ketiga. Kami menggunakan enkripsi untuk melindungi data sensitif.</p>
                </div>
                <div class="faq-item">
                    <h3>Bagaimana cara kerja deteksi otomatis?</h3>
                    <p>Aplikasi membaca notifikasi dari bank/e-wallet yang masuk di HP kamu, lalu AI kami mem-parsing informasi transaksi (jenis, jumlah, merchant) dan membuat catatan transaksi secara otomatis.</p>
                </div>
                <div class="faq-item">
                    <h3>Apakah ini benar-benar gratis?</h3>
                    <p>Ya! Semua fitur Hitungin tersedia gratis tanpa biaya apapun. Tidak ada iklan, tidak ada fitur premium.</p>
                </div>
                <div class="faq-item">
                    <h3>Bisa dipakai di iOS?</h3>
                    <p>Saat ini Hitungin baru tersedia untuk Android. Versi iOS sedang dalam pengembangan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <div class="container">
            <h2>Mulai Atur Keuanganmu</h2>
            <p>Download Hitungin sekarang dan rasakan kemudahan mencatat keuangan secara otomatis.</p>
            <a href="https://github.com/arkastudio462/hitungin/releases/latest" class="btn-download" target="_blank" rel="noopener">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.98-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                Download Sekarang
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} Hitungin. Dibuat dengan ❤️ oleh <a href="https://github.com/arkastudio462" target="_blank" rel="noopener">Arkastudio</a></p>
        </div>
    </footer>
</body>
</html>
