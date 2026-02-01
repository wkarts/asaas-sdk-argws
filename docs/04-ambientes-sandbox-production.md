# 04 — Ambientes (Sandbox e Production)

A SDK expõe dois ambientes via enum `Environment`:

- `Environment::Sandbox` → `https://api-sandbox.asaas.com/v3`
- `Environment::Production` → `https://api.asaas.com/v3`

## Configurando no código (sem .env)

```php
use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

$asaas = new AsaasSdk(new AsaasConfig(
    apiKey: 'SUA_API_KEY_SANDBOX',
    environment: Environment::Sandbox,
    appName: 'MinhaApp/1.0'
));
```

## Alternando em tempo de execução

```php
use Asaas\Sdk\Http\Environment;

$asaas->setEnvironment(Environment::Production);
```

## Boas práticas

- **Nunca** hardcode credenciais de produção no código.
- Use chaves distintas para sandbox e produção.
- Ajuste `timeout` e `connectTimeout` conforme o tipo de operação (ex.: healthcheck vs operações financeiras).
