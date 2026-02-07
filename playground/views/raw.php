<?php
ob_start();
?>
<div class="card">
    <h2>Raw</h2>
    <p>Chame qualquer endpoint direto na API.</p>
    <label>Método</label>
    <select id="method">
        <option>GET</option>
        <option>POST</option>
        <option>PUT</option>
        <option>DELETE</option>
    </select>
    <label>Path</label>
    <input type="text" id="path" value="/customers">
    <label>Query (JSON) — opcional (ex: {"limit":10,"offset":0})</label>
    <textarea id="query" rows="3">{}</textarea>
    <label>Body (JSON)</label>
    <textarea id="body" rows="6">{}</textarea>
    <button id="run">Executar</button>
    <pre id="result"></pre>
</div>
<script>
    const result = document.getElementById('result');
    document.getElementById('run').addEventListener('click', async () => {
        result.textContent = 'Executando...';
        const form = new FormData();
        form.append('method', document.getElementById('method').value);
        form.append('path', document.getElementById('path').value);
        form.append('query', document.getElementById('query').value);
        form.append('body', document.getElementById('body').value);
        const response = await fetch('/raw/run', { method: 'POST', body: form, headers: window.playgroundHeaders() });
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
