<?php
/** @var array $logs */
/** @var array $filters */
/** @var array|null $selected */
ob_start();
?>
<div class="card">
    <h2>Logs</h2>
    <form method="get" action="/logs">
        <label>Filtro de ação</label>
        <input type="text" name="action" value="<?= htmlspecialchars((string) ($filters['action'] ?? '')) ?>">
        <label>Sucesso</label>
        <select name="success">
            <option value="">Todos</option>
            <option value="1" <?= ($filters['success'] ?? '') === '1' ? 'selected' : '' ?>>Sucesso</option>
            <option value="0" <?= ($filters['success'] ?? '') === '0' ? 'selected' : '' ?>>Falha</option>
        </select>
        <button>Filtrar</button>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Ação</th>
                <th>Sucesso</th>
                <th>Duração (ms)</th>
                <th>Detalhes</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars((string) $log['id']) ?></td>
                <td><?= htmlspecialchars((string) $log['created_at']) ?></td>
                <td><?= htmlspecialchars((string) $log['action']) ?></td>
                <td><?= (int) $log['success'] === 1 ? 'Sim' : 'Não' ?></td>
                <td><?= htmlspecialchars((string) $log['duration_ms']) ?></td>
                <td><a href="/logs/<?= urlencode((string) $log['id']) ?>">Ver</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($selected)): ?>
<div class="card">
    <h3>Log #<?= htmlspecialchars((string) $selected['id']) ?></h3>
    <p><strong>Ação:</strong> <?= htmlspecialchars((string) $selected['action']) ?></p>
    <p><strong>Sucesso:</strong> <?= (int) $selected['success'] === 1 ? 'Sim' : 'Não' ?></p>
    <p><strong>Erro:</strong> <?= htmlspecialchars((string) ($selected['error_message'] ?? '')) ?></p>
    <h4>Parâmetros</h4>
    <pre><?= htmlspecialchars($selected['params_json'] ?? '') ?></pre>
    <h4>Resposta</h4>
    <pre><?= htmlspecialchars($selected['response_excerpt'] ?? '') ?></pre>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
