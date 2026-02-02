<?php
/** @var array $catalog */
ob_start();
?>
<div class="card">
    <h2>Explorer</h2>
    <p>Selecione a classe e o método. Parâmetros em JSON (array posicional ou objeto nomeado).</p>
    <label for="class">Classe</label>
    <select id="class"></select>
    <label for="method">Método</label>
    <select id="method"></select>
    <label for="params">Parâmetros (JSON)</label>
    <textarea id="params" rows="6">{}</textarea>
    <label for="file">Upload de arquivo (use o nome do parâmetro esperado)</label>
    <input type="file" id="file">
    <label for="fileName">Nome do parâmetro do arquivo</label>
    <input type="text" id="fileName" placeholder="file">
    <button id="run">Executar</button>
    <pre id="result"></pre>
</div>
<script>
    const catalog = <?= json_encode($catalog, JSON_UNESCAPED_UNICODE) ?>;
    const classSelect = document.getElementById('class');
    const methodSelect = document.getElementById('method');
    const result = document.getElementById('result');

    function populateClasses() {
        classSelect.innerHTML = '';
        (catalog.services || catalog.classes || []).forEach((cls) => {
            const option = document.createElement('option');
            option.value = cls;
            option.textContent = cls;
            classSelect.appendChild(option);
        });
        populateMethods();
    }

    function populateMethods() {
        methodSelect.innerHTML = '';
        const cls = classSelect.value;
        const methods = catalog.methods?.[cls] || [];
        methods.forEach((method) => {
            const option = document.createElement('option');
            option.value = method.name;
            option.textContent = method.name;
            methodSelect.appendChild(option);
        });
    }

    classSelect.addEventListener('change', populateMethods);

    document.getElementById('run').addEventListener('click', async () => {
        result.textContent = 'Executando...';
        const form = new FormData();
        form.append('class', classSelect.value);
        form.append('method', methodSelect.value);
        form.append('params', document.getElementById('params').value);
        const file = document.getElementById('file').files[0];
        const fileName = document.getElementById('fileName').value || 'file';
        if (file) {
            form.append(fileName, file);
        }
        const response = await fetch('/explorer/run', { method: 'POST', body: form, headers: window.playgroundHeaders() });
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
    });

    populateClasses();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
