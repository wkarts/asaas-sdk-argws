<?php

declare(strict_types=1);

namespace Playground;

use Playground\Controllers\DashboardController;
use Playground\Controllers\ExplorerController;
use Playground\Controllers\LogsController;
use Playground\Controllers\RawController;
use Playground\Controllers\ScenariosController;
use Playground\Controllers\SdkProxyController;
use Playground\Controllers\WebhookController;
use Playground\Utils\FileStore;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Routes
{
    public static function register(App $app, Bootstrap $bootstrap): void
    {
        $dashboard = new DashboardController($bootstrap);
        $explorer = new ExplorerController($bootstrap);
        $scenarios = new ScenariosController($bootstrap);
        $webhooks = new WebhookController($bootstrap);
        $logs = new LogsController($bootstrap);
        $raw = new RawController($bootstrap);
        $sdkProxy = new SdkProxyController($bootstrap);

        $app->get('/', [$dashboard, 'index']);
        $app->get('/health', [$dashboard, 'health']);

        $app->get('/sdk/catalog', [$explorer, 'catalog']);
        $app->get('/explorer', [$explorer, 'index']);
        $app->post('/explorer/run', [$explorer, 'run']);

        $app->get('/api/sdk/catalog', [$sdkProxy, 'catalog']);
        // API (proxy) - compat e swagger-friendly
        $app->post('/api/sdk/call/{service}/{method}', [$sdkProxy, 'call']); // legado
        $app->post('/api/sdk/{service}/{method}', [$sdkProxy, 'call']);      // preferido
        $app->get('/openapi.json', [$sdkProxy, 'openapi']);
        $app->get('/swagger', [$sdkProxy, 'swagger']);
        $app->get('/scalar', [$sdkProxy, 'scalar']);
        $app->get('/postman/collection.json', [$sdkProxy, 'postmanCollection']);
        $app->get('/postman/env/{env}.json', [$sdkProxy, 'postmanEnv']);

        $app->get('/scenarios', [$scenarios, 'index']);
        $app->post('/scenarios/run', [$scenarios, 'run']);

        $app->get('/webhooks', [$webhooks, 'index']);
        $app->get('/webhooks/{id}', [$webhooks, 'show']);
        $app->post('/webhooks/{id}/processed', [$webhooks, 'markProcessed']);
        $app->post('/webhooks/asaas', [$webhooks, 'receive']);

        $app->get('/logs', [$logs, 'index']);
        $app->get('/logs/{id}', [$logs, 'show']);

        $app->get('/raw', [$raw, 'index']);
        $app->post('/raw/run', [$raw, 'run']);

        $app->get('/downloads/{name}', [new FileStore($bootstrap->basePath()), 'download']);


        $app->map(['GET','POST','OPTIONS'], '/api/sdk/call/{service}/{method}', function (Request $request, Response $response, array $args) use ($controller) {
            // Preflight CORS (se precisar)
            if (strtoupper($request->getMethod()) === 'OPTIONS') {
                return $response->withStatus(204);
            }
        
            return $controller->handleCall($request, $response, $args);
        });
        
        $app->map(['GET','POST','OPTIONS'], '/api/sdk/{service}/{method}', function (Request $request, Response $response, array $args) use ($controller) {
            if (strtoupper($request->getMethod()) === 'OPTIONS') {
                return $response->withStatus(204);
            }
        
            return $controller->handleCall($request, $response, $args);
        });  
        
    }
}
