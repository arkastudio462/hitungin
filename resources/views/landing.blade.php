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
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg: #ffffff;
            --bg-soft: #f8fafc;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --green: #10b981;
            --red: #ef4444;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
        }

        ::selection {
            background: var(--primary);
            color: white;
        }

        /* ===== Navbar ===== */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            transition: all 0.3s;
        }
        nav.scrolled {
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }
        nav .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 68px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 21px;
            font-weight: 800;
            color: var(--text);
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--text);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: 800;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--text); }
        .nav-cta {
            background: var(--primary);
            color: white !important;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600 !important;
            transition: background 0.2s;
        }
        .nav-cta:hover {
            background: var(--primary-dark);
        }

        /* Mobile hamburger */
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--text);
            cursor: pointer;
            padding: 8px;
        }
        .mobile-menu {
            display: none;
            flex-direction: column;
            padding: 16px 24px 24px;
            gap: 4px;
            border-top: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.98);
        }
        .mobile-menu a {
            color: var(--text-muted);
            text-decoration: none;
            padding: 12px 0;
            font-size: 15px;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }
        .mobile-menu a:last-child { border-bottom: none; }
        .mobile-menu.open { display: flex; }

        /* ===== Hero ===== */
        .hero {
            position: relative;
            padding: 168px 0 96px;
            text-align: center;
            background: var(--bg-soft);
            border-bottom: 1px solid var(--border);
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 7px 16px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 28px;
        }
        .hero-badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 2s infinite;
        }
        .hero h1 {
            font-size: clamp(38px, 6vw, 64px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -2px;
            margin-bottom: 24px;
            color: var(--text);
        }
        .hero h1 .accent {
            color: var(--primary);
        }
        .hero p {
            font-size: clamp(16px, 2vw, 18px);
            color: var(--text-muted);
            max-width: 620px;
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
            gap: 10px;
            background: var(--primary);
            color: white;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        }
        .btn-download:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        /* ===== Phone mockup ===== */
        .hero-visual {
            margin-top: 64px;
            display: flex;
            justify-content: center;
        }
        .phone {
            position: relative;
            width: 300px;
            height: 590px;
            border-radius: 44px;
            padding: 9px;
            background: var(--text);
            box-shadow: 0 32px 64px -20px rgba(15, 23, 42, 0.3);
        }
        .phone-screen {
            width: 100%;
            height: 100%;
            border-radius: 36px;
            background: #f1f5f9;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .phone-notch {
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 96px;
            height: 20px;
            background: var(--text);
            border-radius: 20px;
            z-index: 10;
        }
        .phone-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 38px 18px 12px;
        }
        .phone-app-logo {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 800;
            color: var(--text);
        }
        .phone-app-logo .mini {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            background: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: white;
        }
        .phone-bell {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: var(--text-muted);
            position: relative;
        }
        .phone-bell .badge {
            position: absolute;
            top: -3px;
            right: -3px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: var(--red);
            border: 2px solid #f1f5f9;
            font-size: 7px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .phone-balance {
            text-align: center;
            padding: 8px 18px 16px;
        }
        .phone-balance small {
            color: var(--text-muted);
            font-size: 10px;
        }
        .phone-balance h2 {
            font-size: 24px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.5px;
            margin-top: 2px;
        }
        .phone-chart {
            padding: 0 18px;
            display: flex;
            align-items: flex-end;
            gap: 6px;
            height: 70px;
            margin-bottom: 14px;
        }
        .phone-chart .bar {
            flex: 1;
            border-radius: 6px 6px 3px 3px;
            background: #cbd5e1;
            animation: grow 1s ease-out both;
        }
        .phone-chart .bar:nth-child(odd) {
            background: var(--primary);
        }
        .phone-transactions {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 18px 16px;
        }
        .phone-tx {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 9px 12px;
        }
        .phone-tx .ic {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }
        .phone-tx .ic.income { background: #ecfdf5; color: var(--green); }
        .phone-tx .ic.expense { background: #fef2f2; color: var(--red); }
        .phone-tx .info { flex: 1; min-width: 0; }
        .phone-tx .info b {
            display: block;
            font-size: 10px;
            color: var(--text);
            font-weight: 700;
        }
        .phone-tx .info small {
            font-size: 8px;
            color: var(--text-muted);
        }
        .phone-tx .amount {
            font-size: 10px;
            font-weight: 800;
        }
        .phone-tx .amount.in { color: var(--green); }
        .phone-tx .amount.out { color: var(--red); }

        /* Floating chips */
        .hero-float {
            position: absolute;
            background: #fff;
            border: 1px solid var(--border);
            padding: 10px 16px;
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            animation: float 4s ease-in-out infinite;
            z-index: 2;
        }
        .hero-float .chip-sub {
            display: block;
            font-size: 10px;
            font-weight: 500;
            color: var(--text-muted);
        }
        .hero-float-1 { left: 6%; top: 16%; }
        .hero-float-2 { right: 6%; top: 34%; animation-delay: 1.2s; }
        .hero-float-3 { left: 8%; bottom: 20%; animation-delay: 2.2s; }
        .float-green { color: var(--green); }
        .float-red { color: var(--red); }
        .float-blue { color: var(--primary); }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        @keyframes grow {
            from { height: 20%; }
        }

        /* ===== Section common ===== */
        section {
            padding: 96px 0;
        }
        .section-title {
            text-align: center;
            margin-bottom: 56px;
        }
        .section-title .eyebrow {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 16px;
        }
        .section-title h2 {
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 14px;
            color: var(--text);
        }
        .section-title p {
            font-size: 17px;
            color: var(--text-muted);
            max-width: 540px;
            margin: 0 auto;
        }

        /* ===== Supported banks ===== */
        .banks {
            padding: 40px 0 16px;
        }
        .banks p {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
            font-weight: 500;
        }
        .banks-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }
        .bank-chip {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.2s;
        }
        .bank-chip:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        /* ===== Features ===== */
        .features {
            background: var(--bg-soft);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .feature-card {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 28px;
            border-radius: 16px;
            transition: all 0.25s;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            font-size: 24px;
            background: var(--text);
            color: white;
        }
        .feature-card h3 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text);
        }
        .feature-card p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* ===== How it works ===== */
        .steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            counter-reset: step;
        }
        .step {
            text-align: center;
            padding: 32px 20px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            transition: all 0.25s;
        }
        .step:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .step-number {
            width: 56px;
            height: 56px;
            background: var(--text);
            color: white;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 800;
            margin: 0 auto 18px;
        }
        .step h3 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text);
        }
        .step p {
            font-size: 14px;
            color: var(--text-muted);
            max-width: 260px;
            margin: 0 auto;
        }

        /* ===== Stats ===== */
        .stats {
            background: var(--text);
            color: white;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            text-align: center;
        }
        .stat h3 {
            font-size: clamp(30px, 4vw, 44px);
            font-weight: 800;
            color: white;
        }
        .stat p {
            font-size: 14px;
            color: #94a3b8;
            margin-top: 8px;
        }

        /* ===== Testimonials ===== */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .testimonial {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 26px;
        }
        .testimonial .stars {
            color: var(--primary);
            font-size: 14px;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }
        .testimonial blockquote {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 16px;
        }
        .testimonial .author {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .testimonial .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            color: white;
        }
        .testimonial .author b {
            display: block;
            font-size: 13px;
            color: var(--text);
        }
        .testimonial .author small {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ===== FAQ ===== */
        .faq {
            background: var(--bg-soft);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        .faq-list {
            max-width: 720px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .faq-item {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            transition: border-color 0.25s;
        }
        .faq-item:hover { border-color: var(--primary); }
        .faq-item summary {
            padding: 20px 22px;
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            list-style: none;
        }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary .chev {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--bg-soft);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: transform 0.25s;
        }
        .faq-item[open] summary .chev {
            transform: rotate(45deg);
            background: var(--primary);
            color: white;
        }
        .faq-item .answer {
            padding: 0 22px 20px;
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* ===== CTA ===== */
        .cta-box {
            text-align: center;
            background: var(--bg-soft);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 64px 40px;
        }
        .cta-box h2 {
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 14px;
        }
        .cta-box p {
            font-size: 17px;
            color: var(--text-muted);
            max-width: 480px;
            margin: 0 auto 32px;
        }

        /* ===== Footer ===== */
        footer {
            border-top: 1px solid var(--border);
            padding: 48px 0;
            background: var(--bg);
        }
        .footer-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 24px;
        }
        .footer-brand .logo { margin-bottom: 8px; }
        .footer-brand p {
            font-size: 13px;
            color: var(--text-muted);
            max-width: 320px;
        }
        .footer-links {
            display: flex;
            gap: 28px;
        }
        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }
        .footer-links a:hover { color: var(--text); }
        .footer-bottom {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .footer-bottom p {
            font-size: 13px;
            color: var(--text-muted);
        }
        .footer-bottom a {
            color: var(--primary);
            text-decoration: none;
        }

        /* ===== Reveal animation ===== */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hamburger { display: block; }
            .hero { padding: 132px 0 64px; }
            .steps { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { flex-direction: column; align-items: flex-start; }
            .footer-links { flex-wrap: wrap; gap: 20px; }
            .hero-float-1 { left: 0; top: 8%; }
            .hero-float-2 { right: 0; top: 30%; }
            .hero-float-3 { left: 0; bottom: 14%; }
            section { padding: 64px 0; }
        }
    </style>
    @endverbatim
</head>
<body>
    <!-- Navbar -->
    <nav id="navbar">
        <div class="container">
            <a href="/" class="logo">
                <div class="logo-icon">H</div>
                Hitungin
            </a>
            <ul class="nav-links">
                <li><a href="#fitur">Fitur</a></li>
                <li><a href="#cara-kerja">Cara Kerja</a></li>
                <li><a href="#testimoni">Testimoni</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#download" class="nav-cta">Download</a></li>
            </ul>
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <a href="#fitur">Fitur</a>
            <a href="#cara-kerja">Cara Kerja</a>
            <a href="#testimoni">Testimoni</a>
            <a href="#faq">FAQ</a>
            <a href="#download">Download</a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero" id="download">
        <div class="container">
            <div class="hero-badge reveal">
                <span class="dot"></span>
                v1.0.0 sudah rilis — Gratis!
            </div>
            <h1 class="reveal" style="transition-delay: 0.1s">
                Catat Keuangan<br>
                <span class="accent">Otomatis dari Notifikasi</span>
            </h1>
            <p class="reveal" style="transition-delay: 0.2s">
                Hitungin membaca notifikasi bank dan e-wallet di HP kamu, lalu mencatat setiap transaksi secara otomatis. Tanpa input manual, tanpa ribet.
            </p>
            <div class="hero-buttons reveal" style="transition-delay: 0.3s">
                <a href="https://github.com/arkastudio462/hitungin/releases/latest/download/hitungin.apk" class="btn-download" download>
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Download APK
                </a>
            </div>

            <!-- Phone Mockup -->
            <div class="hero-visual reveal" style="transition-delay: 0.4s">
                <div class="hero-float hero-float-1">
                    <span class="float-green">+ Rp 8.500.000</span>
                    <span class="chip-sub">Gaji masuk</span>
                </div>
                <div class="hero-float hero-float-2">
                    <span class="float-red">- Rp 45.000</span>
                    <span class="chip-sub">Beli makan</span>
                </div>
                <div class="hero-float hero-float-3">
                    <span class="float-blue">92% budget aman</span>
                    <span class="chip-sub">Anggaran bulan ini</span>
                </div>
                <div class="phone">
                    <div class="phone-screen">
                        <div class="phone-notch"></div>
                        <div class="phone-topbar">
                            <div class="phone-app-logo">
                                <span class="mini">H</span>
                                Hitungin
                            </div>
                            <div class="phone-bell">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                <span class="badge">2</span>
                            </div>
                        </div>
                        <div class="phone-balance">
                            <small>Total Saldo</small>
                            <h2>Rp 12.480.000</h2>
                        </div>
                        <div class="phone-chart">
                            <div class="bar" style="height: 45%"></div>
                            <div class="bar" style="height: 70%"></div>
                            <div class="bar" style="height: 55%"></div>
                            <div class="bar" style="height: 85%"></div>
                            <div class="bar" style="height: 65%"></div>
                            <div class="bar" style="height: 95%"></div>
                            <div class="bar" style="height: 75%"></div>
                            <div class="bar" style="height: 100%"></div>
                        </div>
                        <div class="phone-transactions">
                            <div class="phone-tx">
                                <span class="ic income">+</span>
                                <div class="info">
                                    <b>Gaji Bulanan</b>
                                    <small>BCA · Baru saja</small>
                                </div>
                                <span class="amount in">+8,5jt</span>
                            </div>
                            <div class="phone-tx">
                                <span class="ic expense">-</span>
                                <div class="info">
                                    <b>GoFood</b>
                                    <small>GoPay · 2 menit lalu</small>
                                </div>
                                <span class="amount out">-45rb</span>
                            </div>
                            <div class="phone-tx">
                                <span class="ic expense">-</span>
                                <div class="info">
                                    <b>Token Listrik</b>
                                    <small>DANA · 1 jam lalu</small>
                                </div>
                                <span class="amount out">-100rb</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Banks -->
    <section class="banks">
        <div class="container">
            <p class="reveal">Didukung oleh AI parsing untuk bank & e-wallet populer</p>
            <div class="banks-grid reveal" style="transition-delay: 0.1s">
                <span class="bank-chip">BCA</span>
                <span class="bank-chip">Mandiri</span>
                <span class="bank-chip">BRI</span>
                <span class="bank-chip">BNI</span>
                <span class="bank-chip">CIMB Niaga</span>
                <span class="bank-chip">BSI</span>
                <span class="bank-chip">Jenius</span>
                <span class="bank-chip">GoPay</span>
                <span class="bank-chip">OVO</span>
                <span class="bank-chip">DANA</span>
                <span class="bank-chip">ShopeePay</span>
                <span class="bank-chip">LinkAja</span>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="fitur">
        <div class="container">
            <div class="section-title reveal">
                <span class="eyebrow">Fitur</span>
                <h2>Kenapa Pilih Hitungin?</h2>
                <p>Semua yang kamu butuhkan untuk mengelola keuangan, dalam satu aplikasi</p>
            </div>
            <div class="features-grid">
                <div class="feature-card reveal">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </div>
                    <h3>Otomatis dari Notifikasi</h3>
                    <p>Tidak perlu input manual. Hitungin membaca notifikasi bank dan e-wallet lalu mencatat transaksi secara otomatis.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.1s">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    </div>
                    <h3>AI Parsing Cerdas</h3>
                    <p>Menggunakan AI untuk memahami notifikasi dari berbagai bank dan e-wallet dengan akurasi tinggi.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.2s">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </div>
                    <h3>Dashboard Visual</h3>
                    <p>Lihat grafik pemasukan dan pengeluaran kamu secara real-time. Mudah dipahami dan menarik.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3>Budget & Anggaran</h3>
                    <p>Atur anggaran bulanan per kategori. Dapat notifikasi jika pengeluaran mendekati batas.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.1s">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <h3>Aman & Privat</h3>
                    <p>Data keuangan kamu tersimpan aman. Tidak ada data yang dibagikan ke pihak ketiga.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.2s">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
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
            <div class="section-title reveal">
                <span class="eyebrow">Cara Kerja</span>
                <h2>Mulai dalam 3 Langkah Mudah</h2>
                <p>Dari download sampai transaksi tercatat otomatis</p>
            </div>
            <div class="steps">
                <div class="step reveal">
                    <div class="step-number">1</div>
                    <h3>Download & Install</h3>
                    <p>Download APK Hitungin dan install di HP Android kamu. Gratis tanpa ribet.</p>
                </div>
                <div class="step reveal" style="transition-delay: 0.15s">
                    <div class="step-number">2</div>
                    <h3>Hubungkan Akun</h3>
                    <p>Login dengan akun Hitungin kamu. Aktifkan akses notifikasi untuk bank & e-wallet.</p>
                </div>
                <div class="step reveal" style="transition-delay: 0.3s">
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
                <div class="stat reveal">
                    <h3>10+</h3>
                    <p>Bank & E-Wallet</p>
                </div>
                <div class="stat reveal" style="transition-delay: 0.1s">
                    <h3>100%</h3>
                    <p>Gratis</p>
                </div>
                <div class="stat reveal" style="transition-delay: 0.2s">
                    <h3>0</h3>
                    <p>Input Manual</p>
                </div>
                <div class="stat reveal" style="transition-delay: 0.3s">
                    <h3>AI</h3>
                    <p>Parsing Cerdas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" id="testimoni">
        <div class="container">
            <div class="section-title reveal">
                <span class="eyebrow">Testimoni</span>
                <h2>Kata Mereka tentang Hitungin</h2>
                <p>Dipakai oleh ribuan orang untuk mengelola keuangan lebih baik</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial reveal">
                    <div class="stars">★★★★★</div>
                    <blockquote>"Sejak pakai Hitungin, saya nggak pernah lupa catat pengeluaran lagi. Semua tercatat otomatis dari notifikasi e-wallet."</blockquote>
                    <div class="author">
                        <div class="avatar">RA</div>
                        <div>
                            <b>Rizky A.</b>
                            <small>Karyawan Swasta</small>
                        </div>
                    </div>
                </div>
                <div class="testimonial reveal" style="transition-delay: 0.1s">
                    <div class="stars">★★★★★</div>
                    <blockquote>"Fitur budgeting-nya mantap. Ada notifikasi kalau pengeluaran sudah mendekati batas anggaran. Sangat membantu."</blockquote>
                    <div class="author">
                        <div class="avatar">DN</div>
                        <div>
                            <b>Dinda N.</b>
                            <small>Mahasiswa</small>
                        </div>
                    </div>
                </div>
                <div class="testimonial reveal" style="transition-delay: 0.2s">
                    <div class="stars">★★★★★</div>
                    <blockquote>"Gratis tapi fiturnya lengkap. Grafik dan laporannya rapi, jadi gampang lihat ke mana uang saya pergi."</blockquote>
                    <div class="author">
                        <div class="avatar">AP</div>
                        <div>
                            <b>Andi P.</b>
                            <small>Freelancer</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq" id="faq">
        <div class="container">
            <div class="section-title reveal">
                <span class="eyebrow">FAQ</span>
                <h2>Pertanyaan Umum</h2>
                <p>Masih ragu? Berikut jawaban untuk pertanyaan yang paling sering diajukan</p>
            </div>
            <div class="faq-list">
                <details class="faq-item reveal">
                    <summary>
                        Bank dan e-wallet apa saja yang didukung?
                        <span class="chev">+</span>
                    </summary>
                    <div class="answer">
                        Hitungin mendukung BCA, Mandiri, BRI, BNI, CIMB, BSI, Jenius, GoPay, OVO, DANA, ShopeePay, dan LinkAja. Kami terus menambah dukungan bank lainnya.
                    </div>
                </details>
                <details class="faq-item reveal" style="transition-delay: 0.05s">
                    <summary>
                        Apakah data keuangan saya aman?
                        <span class="chev">+</span>
                    </summary>
                    <div class="answer">
                        Ya, data kamu tersimpan di server yang aman dan tidak dibagikan ke pihak ketiga. Kami menggunakan enkripsi untuk melindungi data sensitif.
                    </div>
                </details>
                <details class="faq-item reveal" style="transition-delay: 0.1s">
                    <summary>
                        Bagaimana cara kerja deteksi otomatis?
                        <span class="chev">+</span>
                    </summary>
                    <div class="answer">
                        Aplikasi membaca notifikasi dari bank/e-wallet yang masuk di HP kamu, lalu AI kami mem-parsing informasi transaksi (jenis, jumlah, merchant) dan membuat catatan transaksi secara otomatis.
                    </div>
                </details>
                <details class="faq-item reveal" style="transition-delay: 0.15s">
                    <summary>
                        Apakah ini benar-benar gratis?
                        <span class="chev">+</span>
                    </summary>
                    <div class="answer">
                        Ya! Semua fitur Hitungin tersedia gratis tanpa biaya apapun. Tidak ada iklan, tidak ada fitur premium.
                    </div>
                </details>
                <details class="faq-item reveal" style="transition-delay: 0.2s">
                    <summary>
                        Bisa dipakai di iOS?
                        <span class="chev">+</span>
                    </summary>
                    <div class="answer">
                        Saat ini Hitungin baru tersedia untuk Android. Versi iOS sedang dalam pengembangan.
                    </div>
                </details>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta" id="download">
        <div class="container">
            <div class="cta-box reveal">
                <h2>Mulai Atur Keuanganmu Sekarang</h2>
                <p>Download Hitungin dan rasakan kemudahan mencatat keuangan secara otomatis. Gratis, selamanya.</p>
                <a href="https://github.com/arkastudio462/hitungin/releases/latest/download/hitungin.apk" class="btn-download" download>
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Download Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="logo">
                        <div class="logo-icon">H</div>
                        Hitungin
                    </a>
                    <p>Aplikasi pencatatan keuangan pribadi dengan deteksi otomatis dari notifikasi bank dan e-wallet.</p>
                </div>
                <div class="footer-links">
                    <a href="#fitur">Fitur</a>
                    <a href="#cara-kerja">Cara Kerja</a>
                    <a href="#testimoni">Testimoni</a>
                    <a href="#faq">FAQ</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Hitungin. Dibuat dengan ❤️ oleh <a href="https://github.com/arkastudio462" target="_blank" rel="noopener">Arkastudio</a></p>
                <p>Gratis · Tanpa Iklan · Privat</p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        });

        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobileMenu');
        hamburger.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
        });
        mobileMenu.querySelectorAll('a').forEach((a) => {
            a.addEventListener('click', () => mobileMenu.classList.remove('open'));
        });

        // Reveal on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
    </script>
</body>
</html>