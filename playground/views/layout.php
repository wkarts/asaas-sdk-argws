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
                <a href="/swagger">Swagger</a>
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
                <button class="btn primary" id="openSettings" type="button">Configurar</button>
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
                        <div class="small">Configure logo/favicon/dados do dev em <b>Configurar</b>.</div>
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

    const DEFAULT_LOGO_SVG = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiB2aWV3Qm94PSIwIDAgMjAwIDIwMCI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIHJ4PSIzNiIgZmlsbD0iIzBGMUEyQiIvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjgwIiByPSI0OCIgZmlsbD0iIzVGOENGRiIvPjxwYXRoIGQ9Ik00MCAxNTBoMTIwIiBzdHJva2U9IiNGRkYiIHN0cm9rZS13aWR0aD0iMTIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgb3BhY2l0eT0iMC44NSIvPjx0ZXh0IHg9IjEwMCIgeT0iMTkzIiBmb250LXNpemU9IjIyIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiNFQkYwRkYiIG9wYWNpdHk9IjAuOTIiPlBMQVlHUk9VTkQ8L3RleHQ+PC9zdmc+';
    const DEFAULT_PIX_QR_SVG = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1MDAiIGhlaWdodD0iNTAwIiB2aWV3Qm94PSIwIDAgNTAwIDUwMCI+PHJlY3Qgd2lkdGg9IjUwMCIgaGVpZ2h0PSI1MDAiIHJ4PSIzMiIgZmlsbD0iIzBGMUEyQiIvPjxyZWN0IHg9IjYwIiB5PSI2MCIgd2lkdGg9IjM4MCIgaGVpZ2h0PSIzODAiIHJ4PSIyNCIgZmlsbD0iI0ZGRiIvPjxnIGZpbGw9IiMwQjEyMjAiIG9wYWNpdHk9IjAuOSI+PHJlY3QgeD0iOTAiIHk9IjkwIiB3aWR0aD0iNjAiIGhlaWdodD0iNjAiLz48cmVjdCB4PSIzNTAiIHk9IjkwIiB3aWR0aD0iNjAiIGhlaWdodD0iNjAiLz48cmVjdCB4PSI5MCIgeT0iMzUwIiB3aWR0aD0iNjAiIGhlaWdodD0iNjAiLz48cmVjdCB4PSIxODAiIHk9IjE4MCIgd2lkdGg9IjE0MCIgaGVpZ2h0PSIxNDAiLz48L2c+PHRleHQgeD0iMjUwIiB5PSI0NzAiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iI0VCRjBGRiIgb3BhY2l0eT0iMC44OCI+UUlEIFBJWCAoUExBQ0VIT0xERVIpPC90ZXh0Pjwvc3ZnPg==';

    const DEFAULT_DEV = {
        name: 'ARGWS / SDK PHP (não-oficial)',
        site: 'Contato: (defina no Configurar)',
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
