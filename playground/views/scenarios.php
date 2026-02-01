<?php
ob_start();
?>
<div class="card">
    <h2>Cenários rápidos</h2>
    <p>Executa fluxos comuns. Você pode enviar JSON opcional para ajustar parâmetros.</p>
    <label>Parâmetros (JSON opcional)</label>
    <textarea id="params" rows="4">{}</textarea>
    <div style="display: grid; gap: 8px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <button data-action="create_customer">Criar Cliente</button>
        <button data-action="list_customers" class="secondary">Listar Clientes</button>
        <button data-action="create_payment">Criar Cobrança</button>
        <button data-action="list_payments" class="secondary">Listar Cobranças</button>
        <button data-action="cancel_payment">Cancelar Cobrança</button>
    </div>
    <pre id="result"></pre>
</div>
<script>
    const result = document.getElementById('result');
    document.querySelectorAll('button[data-action]').forEach((button) => {
        button.addEventListener('click', async () => {
            result.textContent = 'Executando...';
            const form = new FormData();
            form.append('action', button.dataset.action);
            form.append('params', document.getElementById('params').value);
            const response = await fetch('/scenarios/run', { method: 'POST', body: form });
            const data = await response.json();
            result.textContent = JSON.stringify(data, null, 2);
        });
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
