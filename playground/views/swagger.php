<?php
ob_start();
?>
<link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
<div class="card">
    <h2>Swagger UI</h2>
    <p>Explore e teste o proxy da SDK via OpenAPI.</p>
    <div id="swagger-ui"></div>
</div>
<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
    window.addEventListener('load', () => {
        SwaggerUIBundle({
            url: '/openapi.json',
            dom_id: '#swagger-ui',
            docExpansion: 'list'
        });
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
