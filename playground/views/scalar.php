<?php
ob_start();
?>
<div class="card">
    <h2>Scalar UI</h2>
    <p>Interface alternativa para testar o proxy via OpenAPI.</p>
    <scalar-api-reference data-url="/openapi.json"></scalar-api-reference>
</div>
<script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
