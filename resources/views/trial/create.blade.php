<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Gratis | Aura ERP MPS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #070012;
            --bg-2: #15022a;
            --bg-3: #28054f;
            --text: #f7f1ff;
            --text-muted: #ccb8ea;
            --primary: #b067ff;
            --secondary: #7bf6ff;
            --panel: rgba(10, 2, 23, 0.78);
            --line: rgba(197, 152, 255, 0.32);
            --danger: #ff9dcc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }

        body {
            font-family: "Space Grotesk", "Segoe UI", Tahoma, sans-serif;
            color: var(--text);
            background:
                radial-gradient(1100px 650px at 7% -12%, rgba(170, 97, 255, 0.34), transparent 66%),
                radial-gradient(1000px 620px at 92% 8%, rgba(123, 246, 255, 0.14), transparent 68%),
                linear-gradient(150deg, var(--bg-1), var(--bg-2) 48%, var(--bg-3));
            padding: 22px;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            opacity: .30;
            background-image:
                linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(circle at center, black 46%, transparent 100%);
        }

        .shell {
            max-width: 980px;
            margin: 0 auto;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: var(--panel);
            backdrop-filter: blur(10px);
            box-shadow: 0 0 0 1px rgba(176, 103, 255, 0.18), 0 26px 58px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid rgba(214, 189, 255, 0.2);
            background: rgba(8, 1, 18, 0.56);
        }

        .dots { display: flex; gap: 7px; }
        .dot { width: 11px; height: 11px; border-radius: 999px; box-shadow: 0 0 10px currentColor; }
        .dot.red { color: #ff6fa6; background: currentColor; }
        .dot.yellow { color: #ffe06c; background: currentColor; }
        .dot.green { color: #7ff8ab; background: currentColor; }

        .status {
            font-family: "Orbitron", sans-serif;
            font-size: 11px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--secondary);
        }

        .content {
            padding: clamp(18px, 2.6vw, 30px);
            display: grid;
            gap: 16px;
        }

        .nav-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 14px;
            border-radius: 10px;
            border: 1px solid rgba(196, 153, 255, 0.34);
            color: var(--text);
            text-decoration: none;
            background: rgba(17, 4, 33, 0.56);
            font-size: 13px;
        }

        h1 {
            font-family: "Orbitron", sans-serif;
            font-size: clamp(26px, 4.1vw, 44px);
            line-height: 1.08;
        }

        .lead {
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 64ch;
        }

        .flash-success {
            border: 1px solid rgba(123, 246, 255, 0.52);
            border-radius: 14px;
            padding: 12px 14px;
            background: rgba(35, 11, 59, 0.72);
            color: #dcfbff;
            font-size: 13px;
            line-height: 1.55;
        }

        .card {
            border: 1px solid rgba(193, 154, 255, 0.35);
            border-radius: 16px;
            background: rgba(8, 1, 20, 0.68);
            padding: 16px;
            display: grid;
            gap: 12px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        .field label {
            font-size: 12px;
            color: #ead8ff;
            letter-spacing: .4px;
        }

        .field input,
        .field select {
            width: 100%;
            min-height: 44px;
            border-radius: 10px;
            border: 1px solid rgba(193, 154, 255, 0.36);
            background: rgba(14, 4, 28, 0.9);
            color: var(--text);
            padding: 10px 11px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .field input:focus,
        .field select:focus {
            border-color: rgba(123, 246, 255, 0.72);
            box-shadow: 0 0 0 3px rgba(123, 246, 255, 0.12);
        }

        .field input.error,
        .field select.error {
            border-color: rgba(255, 135, 195, 0.88);
        }

        .options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .option {
            border: 1px solid rgba(193, 154, 255, 0.32);
            border-radius: 12px;
            padding: 10px;
            background: rgba(17, 6, 32, 0.72);
            display: flex;
            gap: 8px;
            align-items: flex-start;
            cursor: pointer;
        }

        .option input {
            margin-top: 2px;
            accent-color: var(--primary);
        }

        .option strong {
            display: block;
            font-size: 13px;
        }

        .option span {
            display: block;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.35;
            margin-top: 2px;
        }

        .check-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: var(--text-muted);
            font-size: 12px;
        }

        .check-row input {
            margin-top: 2px;
            accent-color: var(--primary);
        }

        .error-text {
            color: var(--danger);
            font-size: 11px;
            line-height: 1.3;
        }

        .submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid rgba(187, 144, 255, 0.72);
            background: linear-gradient(125deg, rgba(176, 103, 255, 0.92), rgba(123, 246, 255, 0.8));
            color: #120028;
            font-family: "Orbitron", sans-serif;
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: none;
            padding: 11px 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .submit:hover {
            filter: brightness(1.06);
            box-shadow: 0 8px 24px rgba(123, 246, 255, 0.28), 0 8px 30px rgba(176, 103, 255, 0.28);
        }

        @media (max-width: 760px) {
            .grid,
            .options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="shell">
        <header class="topbar">
            <div class="dots"><span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span></div>
            <div class="status">Trial Onboarding | Aura ERP MPS</div>
        </header>

        <div class="content">
            <div class="nav-row">
                <a class="btn-link" href="{{ route('erp.blueprint') }}">Voltar para a home</a>
                <a class="btn-link" href="/admin/login">Ja sou cliente</a>
            </div>

            <h1>Teste Gratis por 30 Dias</h1>
            <p class="lead">
                Cadastre sua empresa para ativar o ambiente trial. Informe seu modelo de monitoramento,
                token da Printwayy (quando aplicavel) e banco de faturamento. Antes do fim do periodo,
                o admin recebe aviso com link para ativar a assinatura do Aura.
            </p>

            @if (session('trial_success'))
                <div class="flash-success">
                    <strong>{{ session('trial_success') }}</strong><br>
                    Usuario admin: {{ session('trial_admin_email') }}<br>
                    Acesso: <a href="{{ session('trial_access_url') }}" style="color:#7bf6ff;">{{ session('trial_access_url') }}</a>
                </div>
            @endif

            <form class="card" action="{{ route('trial.start') }}" method="POST" novalidate>
                @csrf

                <div class="field">
                    <label>Monitoramento *</label>
                    <div class="options">
                        <label class="option" for="monitoring_choice_printwayy">
                            <input
                                id="monitoring_choice_printwayy"
                                name="monitoring_choice"
                                type="radio"
                                value="printwayy"
                                @checked(old('monitoring_choice', 'printwayy') === 'printwayy')
                            >
                            <span>
                                <strong>Ja tenho Printwayy</strong>
                                <span>Vou informar o token e conectar agora.</span>
                            </span>
                        </label>

                        <label class="option" for="monitoring_choice_other">
                            <input
                                id="monitoring_choice_other"
                                name="monitoring_choice"
                                type="radio"
                                value="other"
                                @checked(old('monitoring_choice') === 'other')
                            >
                            <span>
                                <strong>Quero implementar outro</strong>
                                <span>Inicio sem token da Printwayy neste momento.</span>
                            </span>
                        </label>
                    </div>
                    @error('monitoring_choice')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div
                    class="field"
                    id="printwayy-token-block"
                    @if (old('monitoring_choice', 'printwayy') !== 'printwayy' && !$errors->has('printwayy_api_token'))
                        style="display:none;"
                    @endif
                >
                    <label for="printwayy_api_token">Token da Printwayy *</label>
                    <input
                        id="printwayy_api_token"
                        name="printwayy_api_token"
                        type="text"
                        value="{{ old('printwayy_api_token') }}"
                        class="@error('printwayy_api_token') error @enderror"
                    >
                    @error('printwayy_api_token')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid">
                    <div class="field">
                        <label for="billing_bank">Banco de faturamento *</label>
                        <select id="billing_bank" name="billing_bank" class="@error('billing_bank') error @enderror" required>
                            <option value="">Selecione o banco</option>
                            @foreach ($billingBanks as $bankKey => $bankLabel)
                                <option value="{{ $bankKey }}" @selected(old('billing_bank') === $bankKey)>{{ $bankLabel }}</option>
                            @endforeach
                        </select>
                        @error('billing_bank')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="company_legal_name">Razao social *</label>
                        <input
                            id="company_legal_name"
                            name="company_legal_name"
                            type="text"
                            value="{{ old('company_legal_name') }}"
                            class="@error('company_legal_name') error @enderror"
                            required
                        >
                        @error('company_legal_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="company_trade_name">Nome fantasia</label>
                        <input
                            id="company_trade_name"
                            name="company_trade_name"
                            type="text"
                            value="{{ old('company_trade_name') }}"
                            class="@error('company_trade_name') error @enderror"
                        >
                        @error('company_trade_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="company_document">CNPJ</label>
                        <input
                            id="company_document"
                            name="company_document"
                            type="text"
                            value="{{ old('company_document') }}"
                            class="@error('company_document') error @enderror"
                        >
                        @error('company_document')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="company_phone">Telefone</label>
                        <input
                            id="company_phone"
                            name="company_phone"
                            type="text"
                            value="{{ old('company_phone') }}"
                            class="@error('company_phone') error @enderror"
                        >
                        @error('company_phone')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="admin_name">Nome do responsavel *</label>
                        <input
                            id="admin_name"
                            name="admin_name"
                            type="text"
                            value="{{ old('admin_name') }}"
                            class="@error('admin_name') error @enderror"
                            required
                        >
                        @error('admin_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="admin_email">Email corporativo *</label>
                        <input
                            id="admin_email"
                            name="admin_email"
                            type="email"
                            value="{{ old('admin_email') }}"
                            class="@error('admin_email') error @enderror"
                            required
                        >
                        @error('admin_email')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Senha admin *</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="@error('password') error @enderror"
                            required
                        >
                        @error('password')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirmar senha *</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="@error('password_confirmation') error @enderror"
                            required
                        >
                        @error('password_confirmation')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <label class="check-row" for="accept_terms">
                    <input
                        id="accept_terms"
                        name="accept_terms"
                        type="checkbox"
                        value="1"
                        @checked(old('accept_terms'))
                    >
                    <span>Eu concordo com os termos e autorizo a criacao da minha conta trial por 30 dias.</span>
                </label>
                @error('accept_terms')
                    <span class="error-text">{{ $message }}</span>
                @enderror

                <button type="submit" class="submit">Ativar trial agora</button>
            </form>
        </div>
    </main>

    <script>
        const choiceInputs = document.querySelectorAll('input[name="monitoring_choice"]');
        const tokenBlock = document.getElementById('printwayy-token-block');
        const tokenInput = document.getElementById('printwayy_api_token');

        function updateTokenVisibility() {
            const selected = document.querySelector('input[name="monitoring_choice"]:checked');
            const needsToken = selected && selected.value === 'printwayy';

            if (needsToken) {
                tokenBlock.style.display = 'grid';
                tokenInput.setAttribute('required', 'required');
                return;
            }

            tokenBlock.style.display = 'none';
            tokenInput.removeAttribute('required');
            tokenInput.value = '';
        }

        choiceInputs.forEach((input) => input.addEventListener('change', updateTokenVisibility));
        updateTokenVisibility();
    </script>
</body>
</html>
