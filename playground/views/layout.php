<?php
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asaas SDK | Playground Operacional (não-oficial)</title>
    <meta name="color-scheme" content="light">
    <link id="dynamicFavicon" rel="icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNyIgZmlsbD0iIzFCMjQzNiIvPgo8Y2lyY2xlIGN4PSIxNiIgY3k9IjE0IiByPSI3IiBmaWxsPSIjNUY4Q0ZGIiBvcGFjaXR5PSIwLjkiLz4KPHJlY3QgeD0iOCIgeT0iMjMiIHdpZHRoPSIxNiIgaGVpZ2h0PSIzIiByeD0iMS41IiBmaWxsPSIjZmZmIiBvcGFjaXR5PSIwLjg1Ii8+Cjwvc3ZnPg==">
    <style>
        :root {
            --bg: #0B1220;
            --surface: #0F1A2B;
            --card: #111E33;
            --card2: #0E192C;
            --text: #E8EEF9;
            --muted: rgba(232, 238, 249, .72);
            --muted2: rgba(232, 238, 249, .55);
            --border: rgba(255,255,255,.10);
            --shadow: 0 10px 30px rgba(0,0,0,.35);
            --primary: #5F8CFF;
            --primary2: #2B59FF;
            --ok: #34D399;
            --warn: #F59E0B;
            --danger: #FB7185;
            --mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        * { box-sizing: border-box; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            margin: 0;
            background: radial-gradient(1200px 800px at 15% 10%, rgba(95,140,255,.16), transparent 55%),
                        radial-gradient(900px 700px at 85% 20%, rgba(52,211,153,.10), transparent 50%),
                        var(--bg);
            color: var(--text);
        }
        a { color: rgba(232,238,249,.92); }
        a:hover { color: #fff; }
        .app { min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            position: sticky; top: 0; z-index: 50;
            background: linear-gradient(180deg, rgba(15,26,43,.98), rgba(15,26,43,.92));
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }
        .topbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 260px;
        }
        .brand-logo {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: rgba(95,140,255,.12);
            border: 1px solid rgba(95,140,255,.25);
            display: grid; place-items: center;
            overflow: hidden;
            flex: 0 0 auto;
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: contain; display: none; }
        .brand-logo .fallback {
            width: 100%; height: 100%;
            display: grid; place-items: center;
            color: rgba(255,255,255,.88);
            font-weight: 800;
            letter-spacing: .4px;
        }
        .brand-title { line-height: 1.15; }
        .brand-title strong { display: block; font-size: 14px; letter-spacing: .2px; }
        .brand-title span { display: block; font-size: 12px; color: var(--muted); margin-top: 2px; }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,.06);
            border: 1px solid var(--border);
            color: rgba(232,238,249,.92);
            font-size: 12px;
            white-space: nowrap;
        }
        .pill .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--ok); box-shadow: 0 0 0 4px rgba(52,211,153,.12); }
        .pill.warn .dot { background: var(--warn); box-shadow: 0 0 0 4px rgba(245,158,11,.15); }
        .pill.danger .dot { background: var(--danger); box-shadow: 0 0 0 4px rgba(251,113,133,.16); }
        .pill.ok .dot { background: var(--ok); box-shadow: 0 0 0 4px rgba(52,211,153,.12); }

        .nav {
            display: flex; flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        .nav a {
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 12px;
            border: 1px solid transparent;
            color: rgba(232,238,249,.86);
            font-size: 13px;
        }
        .nav a:hover {
            background: rgba(255,255,255,.06);
            border-color: var(--border);
            color: #fff;
        }
        .actions { display: flex; align-items: center; gap: 10px; justify-content: flex-end; min-width: 260px; flex-wrap: wrap; }

        .btn {
            appearance: none;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.06);
            color: rgba(232,238,249,.92);
            padding: 9px 12px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }
        .btn:hover { background: rgba(255,255,255,.09); }
        .btn.primary {
            border-color: rgba(95,140,255,.38);
            background: linear-gradient(180deg, rgba(95,140,255,.22), rgba(43,89,255,.18));
        }
        .btn.primary:hover { background: linear-gradient(180deg, rgba(95,140,255,.28), rgba(43,89,255,.22)); }

        main {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 18px 18px 26px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        @media (min-width: 980px) {
            .grid.two { grid-template-columns: 1.25fr .75fr; }
        }

        .card {
            background: linear-gradient(180deg, rgba(17,30,51,.98), rgba(14,25,44,.98));
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: var(--shadow);
        }
        .card h2, .card h3 { margin: 0 0 10px; letter-spacing: .2px; }
        .card p { color: var(--muted); }
        .muted { color: var(--muted); }
        .small { font-size: 12px; color: var(--muted2); }

        .callout {
            border-radius: 16px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.04);
            padding: 14px;
        }
        .callout strong { color: #fff; }
        .callout.warning { border-color: rgba(245,158,11,.35); background: rgba(245,158,11,.08); }
        .callout.danger { border-color: rgba(251,113,133,.35); background: rgba(251,113,133,.08); }
        .callout.ok { border-color: rgba(52,211,153,.30); background: rgba(52,211,153,.06); }

        label { display: block; margin-top: 10px; font-weight: 700; font-size: 12px; color: rgba(232,238,249,.90); }
        input, select, textarea {
            width: 100%;
            padding: 10px 11px;
            margin-top: 6px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: rgba(11,18,32,.55);
            color: rgba(232,238,249,.95);
            outline: none;
        }
        textarea { font-family: var(--mono); font-size: 12px; line-height: 1.35; }
        input:focus, select:focus, textarea:focus { border-color: rgba(95,140,255,.55); box-shadow: 0 0 0 4px rgba(95,140,255,.12); }
        button {
            width: 100%;
            padding: 10px 12px;
            margin-top: 8px;
            border-radius: 12px;
            border: 1px solid rgba(95,140,255,.38);
            background: linear-gradient(180deg, rgba(95,140,255,.22), rgba(43,89,255,.18));
            color: rgba(232,238,249,.94);
            cursor: pointer;
            font-weight: 700;
        }
        button:hover {
            background: linear-gradient(180deg, rgba(95,140,255,.28), rgba(43,89,255,.22));
        }
        button.secondary {
            border-color: rgba(255,255,255,.18);
            background: rgba(255,255,255,.06);
        }
        button.secondary:hover {
            background: rgba(255,255,255,.09);
        }
        pre {
            background: rgba(11,18,32,.65);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
            overflow: auto;
            font-family: var(--mono);
            font-size: 12px;
            color: rgba(232,238,249,.92);
        }
        code { font-family: var(--mono); }

        ul { padding-left: 18px; }
        li { color: rgba(232,238,249,.88); margin: 6px 0; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid var(--border); text-align: left; }
        th { color: rgba(232,238,249,.92); font-weight: 800; font-size: 12px; }
        td { color: rgba(232,238,249,.86); font-size: 13px; }

        .footer {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 18px 18px;
            color: rgba(232,238,249,.62);
            font-size: 12px;
        }
        .footer .line { border-top: 1px solid var(--border); padding-top: 14px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; }
        .kbd { font-family: var(--mono); font-size: 12px; padding: 2px 6px; border-radius: 8px; border: 1px solid var(--border); background: rgba(255,255,255,.05); }
        .copy { display: flex; gap: 8px; align-items: center; }
        .copy input { flex: 1; }
        .row { display:flex; gap: 10px; flex-wrap: wrap; }
        .row .btn { width:auto; }
    </style>
</head>
<body>
<?php
/** @var \Playground\Bootstrap $bootstrap */
$sdkVersion = 'dev';
if (isset($bootstrap) && $bootstrap instanceof \Playground\Bootstrap) {
    $sdkVersion = $bootstrap->sdkVersion();
} elseif (class_exists('Composer\\InstalledVersions')) {
    try {
        /** @var class-string $c */
        $c = 'Composer\\InstalledVersions';
        $p = $c::getPrettyVersion('argws/asaas-sdk-php');
        if (is_string($p) && $p !== '') {
            $sdkVersion = $p;
        }
    } catch (\Throwable) {
    }
}
?>
<div class="app">
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-logo" title="Logo configurável (base64)">
                    <div class="fallback">A</div>
                    <img id="brandLogo" alt="Logo" />
                </div>
                <div class="brand-title">
                    <strong>Asaas SDK • Playground Operacional</strong>
                    <span>Ambiente de demonstração e testes (não-oficial)</span>
                </div>
            </div>

            <nav class="nav" aria-label="Navegação">
                <a href="/">Dashboard</a>
                <a href="/explorer">Explorer</a>
                <a href="/scenarios">Cenários</a>
                <a href="/webhooks">Webhooks</a>
                <a href="/logs">Logs</a>
                <a href="/raw">Raw</a>
                <a href="/swagger">Swagger</a>
            </nav>

            <div class="actions">
                <span id="statusApiPill" class="pill" title="Status do componente API (produção)">
                    <span class="dot"></span>
                    <span>API: <b id="statusApiText">…</b></span>
                </span>
                <span id="statusSandboxPill" class="pill" title="Status do componente Sandbox">
                    <span class="dot"></span>
                    <span>Sandbox: <b id="statusSandboxText">…</b></span>
                </span>
                <a class="pill" href="https://status.asaas.com/" target="_blank" rel="noreferrer" title="Abrir status oficial em nova aba">
                    <span class="dot" style="background: rgba(95,140,255,.92); box-shadow: 0 0 0 4px rgba(95,140,255,.15);"></span>
                    <span>Status</span>
                </a>

                <span id="envPill" class="pill warn" title="Ambiente selecionado">
                    <span class="dot"></span>
                    <span>env: <b id="envText">sandbox</b></span>
                </span>
                <span class="pill" title="Versão da SDK">
                    <span class="dot" style="background: rgba(95,140,255,.92); box-shadow: 0 0 0 4px rgba(95,140,255,.15);"></span>
                    <span>SDK <b><?= htmlspecialchars($sdkVersion, ENT_QUOTES, 'UTF-8') ?></b></span>
                </span>
            </div>
        </div>
    </header>

    <main>
        <div class="grid two">
            <section>
                <div class="card">
                    <div class="callout warning" style="margin-bottom: 12px;">
                        <strong>⚠️ Não é um Playground oficial do Asaas.</strong>
                        <div class="small" style="margin-top: 6px;">
                            Este ambiente existe <b>apenas</b> para demonstrar o uso operacional da <b>SDK PHP (argws/asaas-sdk-php)</b>.
                            Use com cuidado e dê preferência absoluta para <b>homologação / sandbox / testes</b>.
                            Embora exista suporte a chamadas em <b>produção</b>, <b>evite</b> usar produção aqui para reduzir risco de vazamento e exposição de dados.
                        </div>
                    </div>
                    <div class="callout ok">
                        <strong>Recomendação de segurança</strong>
                        <div class="small" style="margin-top: 6px;">
                            Use <span class="kbd">API Key</span> de sandbox sempre que possível.
                            Se precisar testar produção, faça com dados controlados e revise logs/prints.
                            A chave fica armazenada no <b>localStorage</b> do navegador (no seu computador).
                        </div>
                    </div>
                </div>

                <?= $content ?>
            </section>

            <aside>
                <div class="card" id="credentialsCard">
                    <h3 style="margin-bottom: 8px;">Credenciais temporárias</h3>
                    <p class="small" style="margin-top: 0;">
                        A chave é enviada apenas via header e pode ser salva localmente no seu navegador.
                        <b>Preferência:</b> sandbox/testes.
                    </p>

                    <label for="apiKey">API Key</label>
                    <input type="password" id="apiKey" placeholder="Informe a chave (recomendado: sandbox)">

                    <label for="envSelect">Ambiente</label>
                    <select id="envSelect">
                        <option value="sandbox">sandbox (homologação / testes)</option>
                        <option value="production">production (evite)</option>
                    </select>

                    <div class="callout danger" id="prodWarning" style="display:none; margin-top: 12px;">
                        <strong>Produção selecionada</strong>
                        <div class="small" style="margin-top: 6px;">
                            Evite usar produção neste playground. Se prosseguir, use dados controlados.
                        </div>
                    </div>

                    <div style="margin-top: 12px; display: grid; gap: 10px;">
                        <button id="saveCredentials" class="btn" type="button">Salvar no navegador</button>
                        <button id="clearCredentials" class="btn" type="button">Limpar</button>
                    </div>
                </div>

                <!--
                <div class="card">
                    <h3 style="margin-bottom: 8px;">Personalização rápida (somente UI)</h3>
                    <div class="small">Cole base64 (data URI). Nada vai para o servidor: fica no seu navegador.</div>

                    <label for="logoBase64">Logo (data URI)</label>
                    <textarea id="logoBase64" rows="4" placeholder="data:image/png;base64,..."></textarea>
                    <div class="row" style="margin-top: 10px;">
                        <button class="btn primary" id="applyLogo" type="button">Aplicar logo</button>
                        <button class="btn" id="clearLogo" type="button">Remover</button>
                    </div>

                    <label for="pixKeyEdit" style="margin-top: 14px;">Chave Pix</label>
                    <input id="pixKeyEdit" type="text" placeholder="Ex.: wkarts@gmail.com">

                    <label for="pixQrBase64">QR Code Pix (data URI)</label>
                    <textarea id="pixQrBase64" rows="4" placeholder="data:image/png;base64,..."></textarea>
                    <div class="row" style="margin-top: 10px;">
                        <button class="btn primary" id="applyPix" type="button">Salvar Pix</button>
                        <button class="btn" id="clearPix" type="button">Limpar</button>
                    </div>
                </div>
                -->

                <div class="card">
                    <h3 style="margin-bottom: 8px;">Atalhos</h3>
                    <div class="small">Acesso rápido às UIs e endpoints de inspeção.</div>
                    <div style="margin-top: 10px; display: grid; gap: 10px;">
                        <a class="btn" href="/api/sdk/catalog" style="text-align:center; text-decoration:none;">Catálogo SDK (API)</a>
                        <a class="btn" href="/openapi.json" style="text-align:center; text-decoration:none;">OpenAPI JSON</a>
                        <a class="btn" href="/scalar" style="text-align:center; text-decoration:none;">Scalar UI</a>
                        <a class="btn" href="/swagger" style="text-align:center; text-decoration:none;">Swagger UI</a>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 8px;">Sobre / Doações (Pix)</h3>
                    <div class="small" id="developerInfoText"></div>
                    <div style="margin-top: 12px; border-radius: 16px; border: 1px solid var(--border); background: rgba(255,255,255,.03); padding: 12px; display: grid; gap: 10px;">
                        <div class="small">QR Code Pix</div>
                        <img id="pixQr" alt="QR Pix" style="max-width:100%; border-radius: 12px; border: 1px solid var(--border); background: rgba(11,18,32,.45);" />
                        <div class="copy">
                            <input id="pixKey" type="text" readonly>
                            <button class="btn" id="copyPix" type="button" style="width:auto;">Copiar</button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <footer class="footer">
        <div class="line">
            <div>Asaas SDK Playground Operacional (não-oficial) • Use preferencialmente <b>sandbox</b></div>
            <div class="small">Dica: use <span class="kbd">Explorer</span> para chamar qualquer método gerado da SDK.</div>
        </div>
    </footer>
</div>

<script>
    const apiKeyInput = document.getElementById('apiKey');
    const envSelect = document.getElementById('envSelect');
    const savedKey = localStorage.getItem('asaas_api_key');
    const savedEnv = localStorage.getItem('asaas_env');

    const envPill = document.getElementById('envPill');
    const envText = document.getElementById('envText');
    const prodWarning = document.getElementById('prodWarning');

    const dynamicFavicon = document.getElementById('dynamicFavicon');
    const brandLogo = document.getElementById('brandLogo');
    const brandLogoFallback = document.querySelector('.brand-logo .fallback');

    const developerInfoText = document.getElementById('developerInfoText');
    const pixQr = document.getElementById('pixQr');
    const pixKey = document.getElementById('pixKey');

    const clearCredentials = document.getElementById('clearCredentials');

    // quick config inputs
    const logoBase64 = document.getElementById('logoBase64');
    const pixKeyEdit = document.getElementById('pixKeyEdit');
    const pixQrBase64 = document.getElementById('pixQrBase64');

    // status pills
    const statusApiPill = document.getElementById('statusApiPill');
    const statusSandboxPill = document.getElementById('statusSandboxPill');
    const statusApiText = document.getElementById('statusApiText');
    const statusSandboxText = document.getElementById('statusSandboxText');

    const DEFAULT_PIX_QR_SVG = 'data:image/png;base64,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';

    const DEFAULT_DEV = {
        person: 'Wallace Kleiton',
        email: 'wkarts@gmail.com',
        whatsapp: '+55 75 98844-9231',
        company: "WWSoftware's ARGWS Sistemas e Tecnologias",
        note: 'Playground operacional para demonstrar o uso da SDK (não-oficial). Preferência: sandbox/testes.'
    };

    function setEnvUI(env) {
        const isProd = (env || 'sandbox') === 'production';
        envText.textContent = isProd ? 'production' : 'sandbox';
        envPill.classList.toggle('warn', !isProd);
        envPill.classList.toggle('danger', isProd);
        envPill.querySelector('.dot').style.background = isProd ? 'var(--danger)' : 'var(--warn)';
        prodWarning.style.display = isProd ? 'block' : 'none';
    }

    function applyBrandLogo(dataUri) {
        if (dataUri && typeof dataUri === 'string' && dataUri.startsWith('data:')) {
            brandLogo.src = dataUri;
            brandLogo.style.display = 'block';
            brandLogoFallback.style.display = 'none';
            return;
        }
        brandLogo.removeAttribute('src');
        brandLogo.style.display = 'none';
        brandLogoFallback.style.display = 'grid';
    }

    function escapeHtml(str) {
        return String(str || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderDeveloperAndPix() {
        const devPerson = localStorage.getItem('playground_dev_person') || DEFAULT_DEV.person;
        const devEmail = localStorage.getItem('playground_dev_email') || DEFAULT_DEV.email;
        const devWhatsapp = localStorage.getItem('playground_dev_whatsapp') || DEFAULT_DEV.whatsapp;
        const devCompany = localStorage.getItem('playground_dev_company') || DEFAULT_DEV.company;
        const devNote = localStorage.getItem('playground_dev_note') || DEFAULT_DEV.note;

        developerInfoText.innerHTML =
            '<div style="font-weight:900; color:#fff;">' + escapeHtml(devCompany) + '</div>' +
            '<div class="small" style="margin-top:6px;"><b>' + escapeHtml(devPerson) + '</b></div>' +
            '<div class="small" style="margin-top:4px;">Email: ' + escapeHtml(devEmail) + '</div>' +
            '<div class="small" style="margin-top:4px;">WhatsApp: ' + escapeHtml(devWhatsapp) + '</div>' +
            '<div class="small" style="margin-top:8px;">' + escapeHtml(devNote) + '</div>';

        const pixKeySaved = localStorage.getItem('playground_pix_key') || devEmail;
        const pixQrSaved = localStorage.getItem('playground_pix_qr') || DEFAULT_PIX_QR_SVG;
        pixKey.value = pixKeySaved;
        pixQr.src = pixQrSaved;

        // sync editor defaults
        pixKeyEdit.value = pixKeySaved;
        pixQrBase64.value = (localStorage.getItem('playground_pix_qr') || '');
    }

    function setStatusPill(pillEl, textEl, status) {
        // statuspage: operational | degraded_performance | partial_outage | major_outage | maintenance
        const s = String(status || '').toLowerCase();
        let cls = 'ok';
        let label = 'Operational';

        if (s.includes('degraded')) { cls = 'warn'; label = 'Degradado'; }
        else if (s.includes('partial')) { cls = 'danger'; label = 'Parcial'; }
        else if (s.includes('major')) { cls = 'danger'; label = 'Indisponível'; }
        else if (s.includes('maintenance')) { cls = 'warn'; label = 'Manutenção'; }
        else if (s.includes('operational') || s === '') { cls = 'ok'; label = 'Operacional'; }

        pillEl.classList.remove('ok','warn','danger');
        pillEl.classList.add(cls);
        textEl.textContent = label;
    }

    async function refreshAsaasStatus() {
        // Statuspage padrão (Atlassian)
        // Em browsers, normalmente existe: https://status.asaas.com/api/v2/summary.json
        // Se falhar por CORS/bloqueio, mantém "…".
        try {
            const res = await fetch('https://status.asaas.com/api/v2/summary.json', { cache: 'no-store' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            const components = Array.isArray(data.components) ? data.components : [];
            const api = components.find(c => String(c.name || '').toLowerCase() === 'api');
            const sandbox = components.find(c => String(c.name || '').toLowerCase() === 'sandbox');

            if (api && api.status) setStatusPill(statusApiPill, statusApiText, api.status);
            if (sandbox && sandbox.status) setStatusPill(statusSandboxPill, statusSandboxText, sandbox.status);

            // fallback: se não encontrou, tenta por includes
            if (!api) {
                const api2 = components.find(c => String(c.name || '').toLowerCase().includes('api'));
                if (api2 && api2.status) setStatusPill(statusApiPill, statusApiText, api2.status);
            }
            if (!sandbox) {
                const sb2 = components.find(c => String(c.name || '').toLowerCase().includes('sandbox'));
                if (sb2 && sb2.status) setStatusPill(statusSandboxPill, statusSandboxText, sb2.status);
            }
        } catch (e) {
            // mantém "…" mas marca como warn para indicar indisponível localmente
            statusApiPill.classList.remove('ok','danger'); statusApiPill.classList.add('warn');
            statusSandboxPill.classList.remove('ok','danger'); statusSandboxPill.classList.add('warn');
            statusApiText.textContent = 'Indisp.';
            statusSandboxText.textContent = 'Indisp.';
        }
    }

    // initial load
    if (savedKey) apiKeyInput.value = savedKey;
    if (savedEnv) envSelect.value = savedEnv;

    setEnvUI(envSelect.value || 'sandbox');

    const savedLogo = localStorage.getItem('playground_logo_base64') || '';
    applyBrandLogo(savedLogo);
    logoBase64.value = savedLogo;

    // ensure dev defaults are saved once (so você não perde ao limpar cache parcial)
    if (!localStorage.getItem('playground_dev_company')) {
        localStorage.setItem('playground_dev_person', DEFAULT_DEV.person);
        localStorage.setItem('playground_dev_email', DEFAULT_DEV.email);
        localStorage.setItem('playground_dev_whatsapp', DEFAULT_DEV.whatsapp);
        localStorage.setItem('playground_dev_company', DEFAULT_DEV.company);
        localStorage.setItem('playground_dev_note', DEFAULT_DEV.note);
    }

    renderDeveloperAndPix();
    refreshAsaasStatus();
    setInterval(refreshAsaasStatus, 60 * 1000);

    document.getElementById('saveCredentials').addEventListener('click', () => {
        localStorage.setItem('asaas_api_key', apiKeyInput.value);
        localStorage.setItem('asaas_env', envSelect.value);
        setEnvUI(envSelect.value);
    });

    envSelect.addEventListener('change', () => setEnvUI(envSelect.value));

    clearCredentials.addEventListener('click', () => {
        localStorage.removeItem('asaas_api_key');
        localStorage.removeItem('asaas_env');
        apiKeyInput.value = '';
        envSelect.value = 'sandbox';
        setEnvUI('sandbox');
    });

    document.getElementById('applyLogo').addEventListener('click', () => {
        const v = (logoBase64.value || '').trim();
        localStorage.setItem('playground_logo_base64', v);
        applyBrandLogo(v);
    });

    document.getElementById('clearLogo').addEventListener('click', () => {
        localStorage.removeItem('playground_logo_base64');
        logoBase64.value = '';
        applyBrandLogo('');
    });

    document.getElementById('applyPix').addEventListener('click', () => {
        localStorage.setItem('playground_pix_key', (pixKeyEdit.value || '').trim());
        localStorage.setItem('playground_pix_qr', (pixQrBase64.value || '').trim());
        renderDeveloperAndPix();
    });

    document.getElementById('clearPix').addEventListener('click', () => {
        localStorage.removeItem('playground_pix_key');
        localStorage.removeItem('playground_pix_qr');
        pixKeyEdit.value = '';
        pixQrBase64.value = '';
        renderDeveloperAndPix();
    });

    document.getElementById('copyPix').addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(pixKey.value || '');
        } catch (e) {
            pixKey.select();
            document.execCommand('copy');
        }
    });

    // usado por outras páginas (Explorer etc.)
    window.playgroundHeaders = () => ({
        'X-Asaas-Api-Key': apiKeyInput.value || '',
        'X-Asaas-Env': envSelect.value || 'sandbox',
    });
</script>
</body>
</html>
