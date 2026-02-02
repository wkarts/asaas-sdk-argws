<?php
/** @var array $webhooks */
/** @var array|null $selected */
ob_start();
?>
<div class="card">
    <h2>Webhooks</h2>
    <p>Endpoint de recepção: <code>/webhooks/asaas</code></p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Recebido</th>
                <th>Validado</th>
                <th>Processado</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($webhooks as $webhook): ?>
            <tr>
                <td><?= htmlspecialchars((string) $webhook['id']) ?></td>
                <td><?= htmlspecialchars((string) $webhook['received_at']) ?></td>
                <td><span class="badge"><?= (int) $webhook['validated_token'] === 1 ? 'Sim' : 'Não' ?></span></td>
                <td><?= $webhook['processed_at'] ? htmlspecialchars((string) $webhook['processed_at']) : 'Pendente' ?></td>
                <td>
                    <a href="/webhooks/<?= urlencode((string) $webhook['id']) ?>">Ver</a>
                    <?php if (!$webhook['processed_at']): ?>
                        <button class="secondary" data-mark="<?= htmlspecialchars((string) $webhook['id']) ?>">Marcar processado</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($selected)): ?>
<div class="card">
    <h3>Detalhes do Webhook #<?= htmlspecialchars((string) $selected['id']) ?></h3>
    <h4>Headers</h4>
    <pre><?= htmlspecialchars($selected['headers_json'] ?? '') ?></pre>
    <h4>Payload</h4>
    <pre><?= htmlspecialchars($selected['payload_json'] ?? '') ?></pre>
</div>
<?php endif; ?>

<script>
    document.querySelectorAll('[data-mark]').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.mark;
            await fetch(`/webhooks/${id}/processed`, { method: 'POST' });
            window.location.reload();
        });
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
