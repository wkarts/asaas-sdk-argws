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
    <link id="dynamicFavicon" rel="icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48cmVjdCB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHJ4PSI3IiBmaWxsPSIjMUIyNDM2Ii8+PHBhdGggZD0iTTE2IDYuNWMzLjkgMCA3LjEgMy4yIDcuMSA3LjEgMCAzLjktMy4yIDcuMS03LjEgNy4xLTMuOSAwLTcuMS0zLjItNy4xLTcuMSAwLTMuOSAzLjItNy4xIDcuMS03LjFaIiBmaWxsPSIjNUY4Q0ZGIiBvcGFjaXR5PSIwLjkiLz48cGF0aCBkPSJNMjMuNyAyNS41SDEwLjJjLS43IDAtMS4yLS41LTEuMi0xLjIgMC0uNy41LTEuMiAxLjItMS4yaDEzLjVjLjcgMCAxLjIuNSAxLjIgMS4yIDAgLjctLjUgMS4yLTEuMiAxLjJaIiBmaWxsPSIjRkZGIiBvcGFjaXR5PSIwLjg1Ii8+PC9zdmc+">
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
        .actions { display: flex; align-items: center; gap: 10px; justify-content: flex-end; min-width: 260px; }

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

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,.06);
            border: 1px solid var(--border);
            font-size: 12px;
            color: rgba(232,238,249,.92);
        }
        .badge b { color: #fff; }

        /* Drawer / modal */
        .drawer-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.55);
            display: none;
            z-index: 70;
        }
        .drawer {
            position: fixed;
            right: 0; top: 0;
            width: min(520px, 92vw);
            height: 100vh;
            background: linear-gradient(180deg, rgba(17,30,51,.98), rgba(11,18,32,.98));
            border-left: 1px solid var(--border);
            box-shadow: -20px 0 50px rgba(0,0,0,.55);
            transform: translateX(100%);
            transition: transform .22s ease;
            z-index: 80;
            overflow: auto;
        }
        .drawer.open { transform: translateX(0); }
        .drawer-backdrop.open { display: block; }
        .drawer-header {
            padding: 16px 16px 12px;
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0;
            background: rgba(17,30,51,.98);
            backdrop-filter: blur(10px);
            z-index: 1;
        }
        .drawer-body { padding: 14px 16px 18px; }
        .row { display: grid; grid-template-columns: 1fr; gap: 10px; }
        @media (min-width: 520px) { .row.two { grid-template-columns: 1fr 1fr; } }
        .preview {
            width: 100%;
            border-radius: 16px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.03);
            padding: 12px;
            display: grid;
            gap: 10px;
        }
        .preview img { max-width: 100%; border-radius: 12px; border: 1px solid var(--border); background: rgba(11,18,32,.45); }
        .footer {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 18px 18px;
            color: rgba(232,238,249,.62);
            font-size: 12px;
        }
        .footer .line { border-top: 1px solid var(--border); padding-top: 14px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; }
        .kbd { font-family: var(--mono); font-size: 12px; padding: 2px 6px; border-radius: 8px; border: 1px solid var(--border); background: rgba(255,255,255,.05); }
        .copy {
            display: flex; gap: 8px; align-items: center;
        }
        .copy input { flex: 1; }
    </style>
</head>
<body>
<?php
/** @var \Playground\Bootstrap $bootstrap */
// As views são renderizadas no contexto do Slim, mas o autoload já está carregado.
// Usamos a mesma fonte de versão exibida no Swagger/OpenAPI.
$sdkVersion = 'dev';
if (isset($this) && $this instanceof \Playground\Bootstrap) {
    // nunca deve acontecer; apenas defensivo
    $sdkVersion = $this->sdkVersion();
}
// $bootstrap existe em alguns views; se não, tentamos pegar de um global simples.
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
                <!--<a href="/swagger">Swagger</a>-->
            </nav>

            <div class="actions">
                <span id="envPill" class="pill warn" title="Ambiente selecionado">
                    <span class="dot"></span>
                    <span>env: <b id="envText">sandbox</b></span>
                </span>
                <span class="pill" title="Versão da SDK">
                    <span class="dot" style="background: rgba(95,140,255,.92); box-shadow: 0 0 0 4px rgba(95,140,255,.15);"></span>
                    <span>SDK <b><?= htmlspecialchars($sdkVersion, ENT_QUOTES, 'UTF-8') ?></b></span>
                </span>
                <!-- <button class="btn primary" id="openSettings" type="button">Configurar</button> -->
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
                    <div class="preview" style="margin-top: 12px;">
                        <div class="small">QR Code Pix (base64 configurável)</div>
                        <img id="pixQr" alt="QR Pix" />
                        <div class="copy">
                            <input id="pixKey" type="text" readonly>
                            <button class="btn" id="copyPix" type="button" style="width:auto;">Copiar</button>
                        </div>
                        <div class="small">WWSoftWare's <b>ARGWS</b>.</div>
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

<div class="drawer-backdrop" id="drawerBackdrop" aria-hidden="true"></div>
<aside class="drawer" id="drawer" aria-label="Configurações">
    <div class="drawer-header">
        <div style="display:flex; align-items:center; justify-content: space-between; gap: 10px;">
            <div>
                <div style="font-weight: 900; letter-spacing:.2px;">Configurações visuais & informações</div>
                <div class="small">Somente UI (sem mexer nos controllers). Persistido no navegador.</div>
            </div>
            <button class="btn" id="closeSettings" type="button" style="width:auto;">Fechar</button>
        </div>
    </div>
    <div class="drawer-body">
        <div class="callout warning">
            <strong>Segurança</strong>
            <div class="small" style="margin-top: 6px;">
                Evite produção aqui. Priorize sandbox/testes para prevenir vazamento de dados. Este playground não é oficial.
            </div>
        </div>

        <div class="card" style="margin-top: 14px;">
            <h3 style="margin-bottom: 8px;">Logo (base64)</h3>
            <div class="small">Cole uma imagem em base64 (data URI). Ex.: <span class="kbd">data:image/png;base64,...</span></div>
            <label for="logoBase64">Logo (data URI)</label>
            <textarea id="logoBase64" rows="5" placeholder="data:image/png;base64,..."></textarea>
            <div class="row two">
                <button class="btn primary" id="applyLogo" type="button">Aplicar</button>
                <button class="btn" id="clearLogo" type="button">Remover</button>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 8px;">Favicon (base64)</h3>
            <div class="small">Cole um favicon em base64 (data URI). Ex.: <span class="kbd">data:image/svg+xml;base64,...</span></div>
            <label for="faviconBase64">Favicon (data URI)</label>
            <textarea id="faviconBase64" rows="4" placeholder="data:image/svg+xml;base64,..."></textarea>
            <div class="row two">
                <button class="btn primary" id="applyFavicon" type="button">Aplicar</button>
                <button class="btn" id="clearFavicon" type="button">Remover</button>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 8px;">Informações do desenvolvedor</h3>
            <div class="small">Exibidas no painel lateral “Sobre / Doações”.</div>
            <label for="devName">Nome/Marca</label>
            <input id="devName" type="text" placeholder="Ex.: Argws / Wallace">
            <label for="devSite">Site/Contato</label>
            <input id="devSite" type="text" placeholder="Ex.: https://... ou e-mail">
            <label for="devNote">Nota curta</label>
            <input id="devNote" type="text" placeholder="Ex.: SDK não-oficial, suporte sob demanda, etc.">
            <div class="row two" style="margin-top: 10px;">
                <button class="btn primary" id="applyDev" type="button">Salvar</button>
                <button class="btn" id="clearDev" type="button">Limpar</button>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 8px;">Doações via Pix</h3>
            <div class="small">Chave Pix e QR Code em base64 (data URI).</div>
            <label for="pixKeyEdit">Chave Pix</label>
            <input id="pixKeyEdit" type="text" placeholder="Ex.: email@dominio.com ou chave aleatória">
            <label for="pixQrBase64">QR Code Pix (data URI)</label>
            <textarea id="pixQrBase64" rows="5" placeholder="data:image/png;base64,..."></textarea>
            <div class="row two">
                <button class="btn primary" id="applyPix" type="button">Salvar</button>
                <button class="btn" id="clearPix" type="button">Limpar</button>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 8px;">Gerar placeholders automaticamente</h3>
            <div class="small">Cria logo/favicon/QR placeholders simples (base64) para você substituir depois.</div>
            <button class="btn" id="generateDefaults" type="button">Gerar placeholders</button>
        </div>
    </div>
</aside>
<script>
    const apiKeyInput = document.getElementById('apiKey');
    const envSelect = document.getElementById('envSelect');
    const savedKey = localStorage.getItem('asaas_api_key');
    const savedEnv = localStorage.getItem('asaas_env');

    const envPill = document.getElementById('envPill');
    const envText = document.getElementById('envText');
    const prodWarning = document.getElementById('prodWarning');

    const drawer = document.getElementById('drawer');
    const drawerBackdrop = document.getElementById('drawerBackdrop');
    const openSettings = document.getElementById('openSettings');
    const closeSettings = document.getElementById('closeSettings');

    const dynamicFavicon = document.getElementById('dynamicFavicon');
    const DEFAULT_FAVICON_HREF = dynamicFavicon ? dynamicFavicon.getAttribute('href') : '';
    const brandLogo = document.getElementById('brandLogo');
    const brandLogoFallback = document.querySelector('.brand-logo .fallback');

    const developerInfoText = document.getElementById('developerInfoText');
    const pixQr = document.getElementById('pixQr');
    const pixKey = document.getElementById('pixKey');

    const clearCredentials = document.getElementById('clearCredentials');

    // Settings inputs
    const logoBase64 = document.getElementById('logoBase64');
    const faviconBase64 = document.getElementById('faviconBase64');
    const devName = document.getElementById('devName');
    const devSite = document.getElementById('devSite');
    const devNote = document.getElementById('devNote');
    const pixKeyEdit = document.getElementById('pixKeyEdit');
    const pixQrBase64 = document.getElementById('pixQrBase64');

    const DEFAULT_LOGO_SVG = 'data:image/png;base64,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GDhwIEFBQT753bJlS/bs2WP6XUhV31DV81nwzxmXRGS8qhY1/R42bBgFChTw85WwsLCwsMhuctWCau3atalcuTIAu3fvZvv27V7biIiIYMCAAbzyyisUKlQIoCowDmiG/9u7HxgCLAEkLi6OZ599li+//BJV9drvbt26MWbMGHMDWwVgLNAe8O1NwTkngOHAdCD18OHDDBw4kCVLlnjttyd88cUXV65rbuLYsWPEx8dz8OBBfvvtN3766Se2b99OWlpajvkUFBREuXLlqFGjBrfddhvR0dGULl3ar3UsWLCAOXPmZMmG6efdd99NtWrVuOWWW4iMjKR8+fJ+8tI5+/fv59KlS/z+++/s3LmT77//noMHD/p870ZGRrJr1y6PyiYlJVG/fn0SExN9qstX8uXLx4YNG7juuuvclk1ISKBWrVoB+S67okaNGixatMijsitWrODZZ5/1qGz+/Plp3LgxNWvWpEaNGpQoUYJSpUplxdWA8eKLL7JkyZIcqz9XDOiVKlVi9OjRxMTEXPmdqjJt2jSGDx/O33//7bXNqKgoxo4dS48ePQgJCbEBrYG3gMqATVXV5p8dYgp8CwwCtgGsWbOGAQMGsG/fPq+NFStWjOHDh/Pkk0+aEesaAROAmvjhemVo93a7318DbNy4kUGDBvHDDz9ktZqrOHToEDfccEM1oI5fDfuGApeABOA4cBCIBwTg5MmTLF26lNmzZ1/JBZAd1KpVi549e9KqVStuuOEG89c2IAIoaf9XEMiXhWoE+Gr06NHy6quv+mSgUqVK9OzZk4cffpiKFSuavw4CigBlgdJAUSAc/z5fBLiIca2OAoeB8xjXkz/++IPFixfzwQcfcPToUa8M58+fn/Pnz4PxXavgougvwI89evRg5syZXjcgK7Ro0YKVK1cCNMB4hjljM/Bbu3btWLp0abb4ZvLmm28yePBgG9AZ4/o7Y/H8+fMT3AXvql69Os8//zzt27dPnx2zEHATcD1QDMgtWaouAvNy4t7INRQsWJCxY8dy/vx5cx25oojEisibIlJYRDh37hwvv/wykZGRPtVRo0YNNm7cmH59/QUROR2AdepkEflYREqpKpcvX+bdd9+lePHiPvl96623snz5ctPvfCLSW0T+DoDfaSKyUEQqiAjJycl8+umnHikBTzl06BAi8lJWHVX1axY7kxQR+UtE5olI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';
    const DEFAULT_PIX_QR_SVG = 'data:image/png;base64,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';

    const DEFAULT_DEV = {
        name: 'ARGWS / SDK PHP (não-oficial)',
        site: 'https://wwsoftwares.com.br',
        e-Mail: 'wkarts@gmail.com +5575988449231',
        whatsapp: '+5575988449231',
        note: 'Playground operacional para demonstrar o uso da SDK. Preferência: sandbox/testes.'
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

    function applyFavicon(dataUri) {
        if (dataUri && typeof dataUri === 'string' && dataUri.startsWith('data:')) {
            dynamicFavicon.href = dataUri;
        }
    }

    function renderDeveloperAndPix() {
        const dev = {
            name: localStorage.getItem('playground_dev_name') || DEFAULT_DEV.name,
            site: localStorage.getItem('playground_dev_site') || DEFAULT_DEV.site,
            note: localStorage.getItem('playground_dev_note') || DEFAULT_DEV.note,
        };

        developerInfoText.innerHTML =
            '<div style="font-weight:800; color:#fff;">' + escapeHtml(dev.name) + '</div>' +
            '<div class="small" style="margin-top:4px;">' + escapeHtml(dev.site) + '</div>' +
            '<div class="small" style="margin-top:8px;">' + escapeHtml(dev.note) + '</div>';

        const pixKeySaved = localStorage.getItem('playground_pix_key') || '';
        const pixQrSaved = localStorage.getItem('playground_pix_qr') || DEFAULT_PIX_QR_SVG;
        pixKey.value = pixKeySaved;
        pixQr.src = pixQrSaved;
    }

    function openDrawer() {
        drawer.classList.add('open');
        drawerBackdrop.classList.add('open');
        drawerBackdrop.setAttribute('aria-hidden', 'false');
    }

    function closeDrawer() {
        drawer.classList.remove('open');
        drawerBackdrop.classList.remove('open');
        drawerBackdrop.setAttribute('aria-hidden', 'true');
    }

    function escapeHtml(str) {
        return String(str || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    if (savedKey) {
        apiKeyInput.value = savedKey;
    }
    if (savedEnv) {
        envSelect.value = savedEnv;
    }

    setEnvUI(envSelect.value || 'sandbox');

    // Apply persisted UI assets
    const savedLogo = localStorage.getItem('playground_logo_base64') || '';
    const savedFav = localStorage.getItem('playground_favicon_base64') || '';
    applyBrandLogo(savedLogo);
    if (savedFav) {
        applyFavicon(savedFav);
    }
    renderDeveloperAndPix();

    // Populate drawer inputs
    logoBase64.value = savedLogo;
    faviconBase64.value = savedFav;
    devName.value = localStorage.getItem('playground_dev_name') || '';
    devSite.value = localStorage.getItem('playground_dev_site') || '';
    devNote.value = localStorage.getItem('playground_dev_note') || '';
    pixKeyEdit.value = localStorage.getItem('playground_pix_key') || '';
    pixQrBase64.value = localStorage.getItem('playground_pix_qr') || '';

    document.getElementById('saveCredentials').addEventListener('click', () => {
        localStorage.setItem('asaas_api_key', apiKeyInput.value);
        localStorage.setItem('asaas_env', envSelect.value);
        setEnvUI(envSelect.value);
    });

    envSelect.addEventListener('change', () => {
        setEnvUI(envSelect.value);
    });

    clearCredentials.addEventListener('click', () => {
        localStorage.removeItem('asaas_api_key');
        localStorage.removeItem('asaas_env');
        apiKeyInput.value = '';
        envSelect.value = 'sandbox';
        setEnvUI('sandbox');
    });

    openSettings.addEventListener('click', openDrawer);
    closeSettings.addEventListener('click', closeDrawer);
    drawerBackdrop.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });

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

    document.getElementById('applyFavicon').addEventListener('click', () => {
        const v = (faviconBase64.value || '').trim();
        localStorage.setItem('playground_favicon_base64', v);
        applyFavicon(v);
    });
    document.getElementById('clearFavicon').addEventListener('click', () => {
        localStorage.removeItem('playground_favicon_base64');
        faviconBase64.value = '';
        // volta para o padrão do HTML
        if (dynamicFavicon && DEFAULT_FAVICON_HREF) {
            dynamicFavicon.href = DEFAULT_FAVICON_HREF;
        }
    });

    document.getElementById('applyDev').addEventListener('click', () => {
        localStorage.setItem('playground_dev_name', (devName.value || '').trim());
        localStorage.setItem('playground_dev_site', (devSite.value || '').trim());
        localStorage.setItem('playground_dev_note', (devNote.value || '').trim());
        renderDeveloperAndPix();
    });
    document.getElementById('clearDev').addEventListener('click', () => {
        localStorage.removeItem('playground_dev_name');
        localStorage.removeItem('playground_dev_site');
        localStorage.removeItem('playground_dev_note');
        devName.value = '';
        devSite.value = '';
        devNote.value = '';
        renderDeveloperAndPix();
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
            // fallback
            pixKey.select();
            document.execCommand('copy');
        }
    });

    document.getElementById('generateDefaults').addEventListener('click', () => {
        // Apenas placeholders simples (para o usuário substituir)
        if (!logoBase64.value.trim()) {
            logoBase64.value = DEFAULT_LOGO_SVG;
        }
        if (!faviconBase64.value.trim()) {
            // mantém o favicon padrão já no HTML; aqui é só um exemplo editável
            faviconBase64.value = dynamicFavicon.getAttribute('href') || '';
        }
        if (!pixQrBase64.value.trim()) {
            pixQrBase64.value = DEFAULT_PIX_QR_SVG;
        }
        if (!devName.value.trim() && !devSite.value.trim() && !devNote.value.trim()) {
            devName.value = DEFAULT_DEV.name;
            devSite.value = DEFAULT_DEV.site;
            devNote.value = DEFAULT_DEV.note;
        }
    });

    window.playgroundHeaders = () => ({
        'X-Asaas-Api-Key': apiKeyInput.value || '',
        'X-Asaas-Env': envSelect.value || 'sandbox',
    });
</script>
</body>
</html>
