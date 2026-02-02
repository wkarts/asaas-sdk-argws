<?php
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asaas Playground</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f6f7fb; color: #222; }
        header { background: #222; color: #fff; padding: 16px; }
        nav a { color: #fff; margin-right: 12px; text-decoration: none; }
        main { padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 16px; margin-bottom: 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        label { display: block; margin-top: 8px; font-weight: bold; }
        input, select, textarea, button { width: 100%; padding: 8px; margin-top: 4px; border-radius: 4px; border: 1px solid #ccc; }
        button { background: #2b59ff; color: #fff; border: none; cursor: pointer; }
        button.secondary { background: #555; }
        pre { background: #f0f0f0; padding: 12px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; background: #eee; }
    </style>
</head>
<body>
<header>
    <strong>Asaas Playground</strong>
    <nav>
        <a href="/">Dashboard</a>
        <a href="/explorer">Explorer</a>
        <a href="/scenarios">Cenários</a>
        <a href="/webhooks">Webhooks</a>
        <a href="/logs">Logs</a>
        <a href="/raw">Raw</a>
    </nav>
</header>
<main>
    <div class="card">
        <h3>Credenciais temporárias</h3>
        <p>Se quiser usar uma chave pública/temporária, informe abaixo. Ela é enviada apenas por header e não é persistida.</p>
        <label for="apiKey">API Key</label>
        <input type="password" id="apiKey" placeholder="Informe a chave">
        <label for="envSelect">Ambiente</label>
        <select id="envSelect">
            <option value="sandbox">sandbox</option>
            <option value="production">production</option>
        </select>
        <button id="saveCredentials" class="secondary">Salvar localmente</button>
    </div>
    <?= $content ?>
</main>
<script>
    const apiKeyInput = document.getElementById('apiKey');
    const envSelect = document.getElementById('envSelect');
    const savedKey = localStorage.getItem('asaas_api_key');
    const savedEnv = localStorage.getItem('asaas_env');

    if (savedKey) {
        apiKeyInput.value = savedKey;
    }
    if (savedEnv) {
        envSelect.value = savedEnv;
    }

    document.getElementById('saveCredentials').addEventListener('click', () => {
        localStorage.setItem('asaas_api_key', apiKeyInput.value);
        localStorage.setItem('asaas_env', envSelect.value);
    });

    window.playgroundHeaders = () => ({
        'X-Asaas-Api-Key': apiKeyInput.value || '',
        'X-Asaas-Env': envSelect.value || 'sandbox',
    });
</script>
</body>
</html>
