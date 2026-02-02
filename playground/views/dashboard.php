<?php
/** @var string $env */
ob_start();
?>
<div class="card">
    <h2>Dashboard</h2>
    <p>Ambiente: <strong><?= htmlspecialchars($env) ?></strong></p>
    <button id="healthcheck">Healthcheck</button>
    <pre id="healthResult">Clique para testar /health</pre>
</div>
<script>
    document.getElementById('healthcheck').addEventListener('click', async () => {
        const result = document.getElementById('healthResult');
        result.textContent = 'Executando...';
        const response = await fetch('/health', { headers: window.playgroundHeaders() });
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
