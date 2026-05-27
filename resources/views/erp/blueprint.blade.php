<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura ERP MPS | Laravel 12 Blueprint</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #060010;
            --bg-2: #15042b;
            --bg-3: #2b0550;
            --text: #f7f1ff;
            --muted: #cdb9ea;
            --line: rgba(213, 182, 255, 0.22);
            --panel: rgba(13, 3, 26, 0.8);
            --purple: #b067ff;
            --cyan: #74efff;
            --pink: #ff9ad4;
            --ok: #8dffb4;
            --radius-xl: 24px;
            --radius-lg: 16px;
            --radius-md: 12px;
            --shadow-main: 0 0 0 1px rgba(176, 103, 255, 0.24), 0 24px 55px rgba(0, 0, 0, 0.55);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }

        body {
            font-family: "Space Grotesk", "Segoe UI", Tahoma, sans-serif;
            color: var(--text);
            background:
                radial-gradient(860px 520px at 8% -8%, rgba(182, 108, 255, 0.28), transparent 66%),
                radial-gradient(1000px 640px at 86% 0%, rgba(116, 239, 255, 0.16), transparent 65%),
                linear-gradient(146deg, var(--bg-1), var(--bg-2) 46%, var(--bg-3));
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            opacity: .3;
            background-image:
                linear-gradient(rgba(255, 255, 255, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .045) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(circle at center, black 44%, transparent 100%);
        }

        .app {
            max-width: 1260px;
            margin: 0 auto;
            padding: 22px;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity .6s ease, transform .6s ease;
        }

        .app.ready {
            opacity: 1;
            transform: translateY(0);
        }

        .frame {
            border: 1px solid rgba(197, 152, 255, 0.35);
            border-radius: var(--radius-xl);
            overflow: hidden;
            background: var(--panel);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-main);
        }

        .topbar {
            min-height: 64px;
            padding: 12px 18px;
            border-bottom: 1px solid var(--line);
            background: rgba(8, 1, 17, 0.66);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .dots { display: flex; gap: 8px; }
        .dot {
            width: 11px;
            height: 11px;
            border-radius: 999px;
            box-shadow: 0 0 12px currentColor;
        }
        .dot.red { color: #ff6da2; background: currentColor; }
        .dot.yellow { color: #ffd96f; background: currentColor; }
        .dot.green { color: #84f9ae; background: currentColor; }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-mark {
            width: 32px;
            height: 32px;
            border-radius: 11px;
            border: 1px solid rgba(199, 157, 255, 0.55);
            background: linear-gradient(145deg, rgba(176, 103, 255, 0.24), rgba(116, 239, 255, 0.12));
            display: grid;
            place-items: center;
            box-shadow: inset 0 0 14px rgba(176, 103, 255, 0.28), 0 0 18px rgba(176, 103, 255, 0.25);
        }

        .logo-mark svg {
            width: 19px;
            height: 19px;
            display: block;
            filter: drop-shadow(0 0 10px rgba(123, 246, 255, 0.45));
        }

        .brand strong {
            font-family: "Orbitron", sans-serif;
            letter-spacing: .7px;
            font-size: 14px;
        }

        .status {
            font-family: "Orbitron", sans-serif;
            font-size: 11px;
            color: var(--cyan);
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: right;
        }

        .hero {
            padding: clamp(22px, 3.5vw, 42px);
            border-bottom: 1px solid var(--line);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 22px;
            align-items: center;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            margin-bottom: 18px;
            border-radius: 999px;
            border: 1px solid rgba(211, 181, 255, 0.52);
            background: rgba(176, 103, 255, 0.12);
            padding: 8px 13px;
            font-family: "Orbitron", sans-serif;
            font-size: 11px;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .hero h1 {
            font-family: "Orbitron", sans-serif;
            font-size: clamp(42px, 6vw, 84px);
            line-height: 1.02;
            text-wrap: balance;
        }

        .hero h1 .grad {
            background: linear-gradient(90deg, #ffffff 0%, #dfc2ff 45%, #7cf0ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero p {
            margin-top: 18px;
            color: var(--muted);
            line-height: 1.6;
            max-width: 72ch;
            font-size: 18px;
        }

        .hero-actions {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-primary,
        .btn-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 10px 16px;
            border-radius: 12px;
            text-decoration: none;
            transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
        }

        .btn-primary {
            border: 1px solid rgba(187, 144, 255, 0.72);
            background: linear-gradient(120deg, rgba(176, 103, 255, .96), rgba(116, 239, 255, .8));
            color: #120028;
            font-family: "Orbitron", sans-serif;
            font-size: 12px;
            letter-spacing: .9px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .btn-ghost {
            border: 1px solid rgba(200, 160, 255, .42);
            background: rgba(16, 4, 31, .58);
            color: var(--text);
            font-size: 14px;
        }

        .btn-primary:hover,
        .btn-ghost:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: 0 10px 24px rgba(123, 246, 255, 0.22), 0 8px 24px rgba(176, 103, 255, 0.22);
        }

        .kpis {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(4, minmax(130px, 1fr));
            gap: 10px;
        }

        .kpi {
            border-radius: var(--radius-md);
            border: 1px solid rgba(195, 154, 255, 0.34);
            background: rgba(11, 2, 20, .65);
            padding: 12px;
        }

        .kpi strong {
            display: block;
            font-family: "Orbitron", sans-serif;
            font-size: clamp(24px, 3vw, 34px);
            color: #e7ceff;
        }

        .kpi span {
            display: block;
            color: var(--muted);
            font-size: 13px;
            margin-top: 5px;
        }

        .visual-card {
            position: relative;
            border-radius: 24px;
            border: 1px solid rgba(200, 162, 255, .45);
            background: linear-gradient(160deg, rgba(17, 5, 31, .96), rgba(13, 2, 24, .86));
            padding: 20px;
            overflow: hidden;
            min-height: 420px;
        }

        .visual-card::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 999px;
            top: -80px;
            right: -70px;
            background: radial-gradient(circle at center, rgba(116, 239, 255, .26), transparent 70%);
            filter: blur(4px);
        }

        .visual-card::after {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            border-radius: 999px;
            left: -90px;
            bottom: -160px;
            background: radial-gradient(circle at center, rgba(176, 103, 255, .32), transparent 70%);
        }

        .visual-head {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 14px;
        }

        .visual-title {
            font-family: "Orbitron", sans-serif;
            font-size: 34px;
            line-height: 1.1;
            max-width: 14ch;
        }

        .pulse-chip {
            border-radius: 999px;
            border: 1px solid rgba(177, 240, 255, .5);
            background: rgba(8, 31, 39, .5);
            color: #d5f9ff;
            padding: 6px 10px;
            font-size: 12px;
            white-space: nowrap;
        }

        .phone-shot {
            position: relative;
            z-index: 2;
            border-radius: 18px;
            border: 1px solid rgba(197, 158, 255, .45);
            overflow: hidden;
            box-shadow: 0 24px 40px rgba(0, 0, 0, .45), 0 0 45px rgba(116, 239, 255, .18);
            width: min(420px, 100%);
            margin: 8px auto 0;
        }

        .phone-shot img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }

        .preview-row {
            position: relative;
            z-index: 2;
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .preview-thumb {
            border-radius: 12px;
            border: 1px solid rgba(191, 152, 255, .35);
            overflow: hidden;
            background: rgba(10, 2, 20, .64);
            min-height: 110px;
        }

        .preview-thumb img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .visual-mini {
            position: relative;
            z-index: 2;
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .mini {
            border-radius: 10px;
            border: 1px solid rgba(191, 152, 255, .35);
            background: rgba(10, 2, 20, .64);
            padding: 10px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.35;
        }

        .mini strong {
            display: block;
            color: #ead6ff;
            font-family: "Orbitron", sans-serif;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .section {
            padding: clamp(20px, 3vw, 30px);
            border-bottom: 1px solid var(--line);
        }

        .section:last-child { border-bottom: none; }

        .section-head {
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            align-items: baseline;
        }

        .section h2 {
            font-family: "Orbitron", sans-serif;
            font-size: clamp(22px, 3vw, 34px);
        }

        .section small {
            font-family: "Orbitron", sans-serif;
            font-size: 11px;
            letter-spacing: 1.6px;
            color: var(--muted);
            text-transform: uppercase;
        }

        .plan-grid,
        .showcase-grid,
        .stack-grid {
            display: grid;
            gap: 12px;
        }

        .plan-grid { grid-template-columns: repeat(3, minmax(180px, 1fr)); }
        .showcase-grid { grid-template-columns: repeat(3, minmax(220px, 1fr)); }
        .stack-grid { grid-template-columns: repeat(4, minmax(180px, 1fr)); }

        .card {
            border-radius: var(--radius-lg);
            border: 1px solid rgba(194, 154, 255, 0.3);
            background: rgba(9, 1, 20, 0.6);
            padding: 14px;
        }

        .card h3 {
            color: #f5e8ff;
            font-size: 15px;
            margin-bottom: 9px;
        }

        .card p {
            color: var(--muted);
            line-height: 1.52;
            font-size: 13px;
        }

        .price {
            font-family: "Orbitron", sans-serif;
            font-size: 21px;
            color: #ebd2ff;
            margin-bottom: 8px;
        }

        .list {
            list-style: none;
            display: grid;
            gap: 7px;
        }

        .list li {
            border-left: 2px solid rgba(176, 103, 255, .55);
            padding-left: 10px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .shot {
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid rgba(192, 154, 255, 0.32);
            background: rgba(10, 2, 21, .66);
            transition: transform .17s ease, box-shadow .17s ease, border-color .17s ease;
        }

        .shot:hover {
            transform: translateY(-2px);
            border-color: rgba(124, 240, 255, 0.5);
            box-shadow: 0 14px 28px rgba(0, 0, 0, .34);
        }

        .shot img {
            width: 100%;
            display: block;
            object-fit: cover;
            aspect-ratio: 16/10;
        }

        .shot-body {
            padding: 12px;
            display: grid;
            gap: 6px;
        }

        .shot-body h3 {
            font-size: 14px;
            color: #f2e3ff;
        }

        .shot-body p {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .footer {
            padding: 13px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            background: rgba(8, 1, 18, .62);
            border-top: 1px solid var(--line);
        }

        .reveal {
            opacity: 0;
            transform: translateY(12px);
            transition: opacity .55s ease, transform .55s ease;
        }

        .reveal.on {
            opacity: 1;
            transform: translateY(0);
        }

        .splash {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 52% 36%, rgba(176, 103, 255, .23), transparent 58%),
                radial-gradient(circle at 44% 58%, rgba(116, 239, 255, .15), transparent 62%),
                linear-gradient(160deg, #080012, #16042d 56%, #2b0550);
            transition: opacity .55s ease, visibility .55s ease;
        }

        .splash.hide {
            opacity: 0;
            visibility: hidden;
        }

        .splash-box {
            width: min(620px, 92vw);
            border-radius: 26px;
            border: 1px solid rgba(211, 178, 255, .48);
            background: rgba(14, 3, 29, .8);
            padding: 32px 24px;
            box-shadow: 0 0 0 1px rgba(177, 119, 255, .22), 0 24px 52px rgba(0, 0, 0, .5);
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .splash-logo {
            width: 132px;
            height: 132px;
            margin: 0 auto 16px;
            border-radius: 34px;
            border: 1px solid rgba(206, 169, 255, .56);
            background: radial-gradient(circle at 26% 18%, rgba(180, 120, 255, .3), rgba(13, 4, 26, .95));
            display: grid;
            place-items: center;
            position: relative;
            box-shadow: inset 0 0 30px rgba(176, 103, 255, .22), 0 0 34px rgba(116, 239, 255, .16);
        }

        .splash-logo::before,
        .splash-logo::after {
            content: "";
            position: absolute;
            inset: -12px;
            border-radius: 40px;
            border: 1px solid rgba(116, 239, 255, .28);
            animation: pulseRing 2.2s ease-out infinite;
        }

        .splash-logo::after {
            inset: -24px;
            animation-delay: .7s;
            border-color: rgba(176, 103, 255, .26);
        }

        @keyframes pulseRing {
            0% { transform: scale(.92); opacity: .85; }
            100% { transform: scale(1.08); opacity: 0; }
        }

        .splash-logo svg {
            width: 70px;
            height: 70px;
            filter: drop-shadow(0 0 12px rgba(116, 239, 255, .45));
        }

        .splash-kicker {
            font-family: "Orbitron", sans-serif;
            font-size: 12px;
            letter-spacing: 2px;
            color: var(--cyan);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .splash h1 {
            font-family: "Orbitron", sans-serif;
            font-size: clamp(30px, 5vw, 50px);
            line-height: 1.04;
            margin-bottom: 8px;
        }

        .splash p {
            color: var(--muted);
            margin-bottom: 18px;
        }

        .splash-track {
            height: 9px;
            border-radius: 999px;
            border: 1px solid rgba(212, 176, 255, .4);
            background: rgba(255, 255, 255, .06);
            overflow: hidden;
        }

        .splash-track span {
            display: block;
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #7bf6ff 0%, #b067ff 48%, #ffa7d7 100%);
            box-shadow: 0 0 18px rgba(123, 246, 255, .85);
            animation: loadSplash 2.25s cubic-bezier(.23, 1, .32, 1) forwards;
        }

        @keyframes loadSplash {
            from { width: 0; }
            to { width: 100%; }
        }

        @media (max-width: 1100px) {
            .hero-grid { grid-template-columns: 1fr; }
            .visual-card { min-height: 0; }
            .plan-grid,
            .showcase-grid,
            .stack-grid { grid-template-columns: 1fr 1fr; }
            .kpis { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 700px) {
            .app { padding: 14px; }
            .topbar { padding: 10px 12px; }
            .brand strong { font-size: 12px; }
            .status { font-size: 10px; }
            .plan-grid,
            .showcase-grid,
            .stack-grid,
            .kpis,
            .visual-mini,
            .preview-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="splash" id="splash-screen" role="status" aria-live="polite" aria-label="Inicializando Aura ERP">
        <div class="splash-box">
            <div class="splash-logo" aria-hidden="true">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 8L91 92H73L65 75H35L27 92H9L50 8Z" fill="url(#auraGradSplash)"/>
                    <rect x="40" y="53" width="20" height="9" rx="4.5" fill="#0d021b"/>
                    <defs>
                        <linearGradient id="auraGradSplash" x1="10" y1="12" x2="92" y2="92" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#EED7FF"/>
                            <stop offset="0.55" stop-color="#B067FF"/>
                            <stop offset="1" stop-color="#74EFFF"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="splash-kicker">Aura ERP MPS</div>
            <h1>Control Matrix Online</h1>
            <p>Monitoramento, chamados e faturamento em um unico ambiente.</p>
            <div class="splash-track"><span></span></div>
        </div>
    </div>

    <main class="app" id="app">
        <div class="frame">
            <header class="topbar">
                <div class="topbar-left">
                    <div class="dots"><span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span></div>
                    <div class="brand">
                        <span class="logo-mark" aria-hidden="true">
                            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M50 8L91 92H73L65 75H35L27 92H9L50 8Z" fill="url(#auraGradTop)"/>
                                <rect x="40" y="53" width="20" height="9" rx="4.5" fill="#0d021b"/>
                                <defs>
                                    <linearGradient id="auraGradTop" x1="10" y1="12" x2="92" y2="92" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#EED7FF"/>
                                        <stop offset="0.55" stop-color="#B067FF"/>
                                        <stop offset="1" stop-color="#74EFFF"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </span>
                        <strong>Aura ERP MPS</strong>
                    </div>
                </div>
                <div class="status">Blueprint Runtime | Laravel 12</div>
            </header>

            <section class="hero reveal">
                <div class="hero-grid">
                    <div>
                        <span class="tag">ERP SaaS Multiempresa | MPS + Printwayy</span>
                        <h1><span class="grad">Sempre conectado</span></h1>
                        <p>
                            Aura ERP MPS conecta monitoramento, chamados tecnicos, suprimentos e faturamento em um fluxo unico
                            para sua locadora operar com velocidade e previsibilidade.
                        </p>
                        <div class="hero-actions">
                            <a class="btn-primary" href="{{ route('trial.create') }}">Comecar teste de 30 dias</a>
                            <a class="btn-ghost" href="/admin/login">Ja sou cliente</a>
                        </div>
                        <div class="kpis">
                            <article class="kpi"><strong>20</strong><span>Capitulos estrategicos cobertos</span></article>
                            <article class="kpi"><strong>12+</strong><span>Modulos operacionais integrados</span></article>
                            <article class="kpi"><strong>90d</strong><span>MVP orientado a faturamento</span></article>
                            <article class="kpi"><strong>1 DB</strong><span>Multi-tenant por tenant_id</span></article>
                        </div>
                    </div>

                    <aside class="visual-card">
                        <div class="visual-head">
                            <h3 class="visual-title">Sempre conectado</h3>
                            <span class="pulse-chip">Online agora</span>
                        </div>
                        <div class="phone-shot">
                            <img src="{{ asset('images/aura-prints/chamados.png') }}" alt="Tela real de chamados tecnicos no Aura ERP MPS" loading="lazy">
                        </div>
                        <div class="preview-row">
                            <div class="preview-thumb">
                                <img src="{{ asset('images/aura-prints/suprimentos.png') }}" alt="Tela real de suprimentos no Aura ERP MPS" loading="lazy">
                            </div>
                            <div class="preview-thumb">
                                <img src="{{ asset('images/aura-prints/faturamento.png') }}" alt="Tela real de faturamento no Aura ERP MPS" loading="lazy">
                            </div>
                        </div>
                        <div class="visual-mini">
                            <div class="mini"><strong>+42%</strong>Reducao de tempo operacional com workflow centralizado.</div>
                            <div class="mini"><strong>24/7</strong>Visao continua de parque, SLA e financeiro por contrato.</div>
                        </div>
                    </aside>
                </div>
            </section>

            <section class="section reveal">
                <div class="section-head"><h2>Planos SaaS Aura</h2><small>preco competitivo para MPS vertical</small></div>
                <div class="plan-grid">
                    @foreach ($plans as $plan)
                        @php
                            $planName = match($plan['key']) {
                                'start' => 'Start',
                                'pro' => 'Pro',
                                'enterprise' => 'Enterprise',
                                default => strtoupper((string) $plan['key']),
                            };
                            $equipmentLabel = is_numeric($plan['equipment_limit'])
                                ? 'Ate '.(int) $plan['equipment_limit'].' equipamentos'
                                : 'Equipamentos ilimitados';
                        @endphp
                        <article class="card">
                            <h3>{{ $planName }}</h3>
                            <div class="price">{{ $plan['monthly_price_label'] }}</div>
                            <ul class="list">
                                <li>{{ $equipmentLabel }}</li>
                                <li>Onboarding de 30 dias com foco em faturamento automatico.</li>
                                <li>Monitoramento, chamados e financeiro no mesmo fluxo.</li>
                            </ul>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="section reveal">
                <div class="section-head"><h2>Aura em Acao</h2><small>prints reais do sistema</small></div>
                <div class="hero-actions" style="margin:0 0 14px 0;">
                    <a class="btn-primary" href="{{ route('trial.create') }}">Quero testar com minha locadora</a>
                    <a class="btn-ghost" href="/admin/login">Entrar no painel</a>
                </div>
                <div class="showcase-grid">
                    <article class="shot">
                        <img src="{{ asset('images/aura-prints/chamados.png') }}" alt="Tela de chamados tecnicos no Aura ERP MPS" loading="lazy">
                        <div class="shot-body">
                            <h3>SLA e Atendimento Tecnico</h3>
                            <p>Fila de chamados com prioridade, origem e cliente para acelerar despacho tecnico.</p>
                        </div>
                    </article>
                    <article class="shot">
                        <img src="{{ asset('images/aura-prints/suprimentos.png') }}" alt="Tela de suprimentos no Aura ERP MPS" loading="lazy">
                        <div class="shot-body">
                            <h3>Controle de Suprimentos</h3>
                            <p>Gestao de toner, fusor e pecas com minimo x atual para evitar ruptura.</p>
                        </div>
                    </article>
                    <article class="shot">
                        <img src="{{ asset('images/aura-prints/faturamento.png') }}" alt="Tela de faturamento no Aura ERP MPS" loading="lazy">
                        <div class="shot-body">
                            <h3>Financeiro por Contrato</h3>
                            <p>Faturas por referencia, status e vencimento para manter caixa previsivel.</p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="section reveal">
                <div class="section-head"><h2>Modulo que vende resultado</h2><small>valor direto para MPS</small></div>
                <div class="stack-grid">
                    <article class="card"><h3>Faturamento automatico</h3><p>Leitura, franquia e excedente convertidos em cobranca sem trabalho manual.</p></article>
                    <article class="card"><h3>Chamados inteligentes</h3><p>Abertura por alerta, triagem rapida e cumprimento de SLA com rastreio completo.</p></article>
                    <article class="card"><h3>Rentabilidade por contrato</h3><p>Clareza sobre lucro real por cliente para corrigir contratos negativos.</p></article>
                    <article class="card"><h3>Operacao centralizada</h3><p>Monitoramento, tecnico, estoque e financeiro em uma unica camada SaaS.</p></article>
                </div>
            </section>

            <footer class="footer">
                Seu contador vira faturamento automatico | Aura ERP MPS | Laravel 12
            </footer>
        </div>
    </main>

    <script>
        const splash = document.getElementById('splash-screen');
        const app = document.getElementById('app');

        window.addEventListener('load', () => {
            setTimeout(() => {
                splash.classList.add('hide');
                app.classList.add('ready');
            }, 2400);
        });

        const observer = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('on');
                    observer.unobserve(entry.target);
                }
            }
        }, { threshold: 0.15 });

        document.querySelectorAll('.reveal').forEach((element, index) => {
            element.style.transitionDelay = `${Math.min(index * 65, 300)}ms`;
            observer.observe(element);
        });
    </script>
</body>
</html>
