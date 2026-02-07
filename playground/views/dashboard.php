<?php
/** @var string $env */
ob_start();
?>
<div class="card">
    <h2>Dashboard</h2>
    <p>Ambiente: <strong><?= htmlspecialchars($env) ?></strong></p>
    <p>Links rápidos:</p>
    <ul>
        <li><a href="/explorer">Explorer</a></li>
        <li><a href="/api/sdk/catalog">Catálogo SDK (API)</a></li>
        <li><a href="/swagger">Swagger UI</a></li>
        <li><a href="/scalar">Scalar UI</a></li>
        <li><a href="/openapi.json">OpenAPI JSON</a></li>
        <li><a href="/postman/collection.json">Postman Collection</a></li>
    </ul>
    <button id="healthcheck">Healthcheck</button>
    <pre id="healthResult">Clique para testar /health</pre>
</div>
<script>
    document.getElementById('healthcheck').addEventListener('click', async () => {
        const result = document.getElementById('healthResult');
        result.textContent = 'Executando...';
        try {
            const response = await fetch('/health', { headers: window.playgroundHeaders() });
            const text = await response.text();
            try {
                const data = JSON.parse(text || '{}');
                result.textContent = JSON.stringify(data, null, 2);
            } catch (e) {
                result.textContent = 'Resposta não-JSON (HTTP ' + response.status + '):\n' + text;
            }
        } catch (e) {
            result.textContent = 'Falha ao chamar /health: ' + (e && e.message ? e.message : String(e));
        }
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
