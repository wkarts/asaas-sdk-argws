# Asaas SDK PHP (NÃO OFICIAL)

> **NÃO OFICIAL** — Não afiliada ao Asaas.  
> Baseada na SDK Java oficial e na documentação pública/OpenAPI.  
> Asaas é marca de seus respectivos proprietários.

SDK PHP comunitária para a API do Asaas, com foco em paridade com a SDK Java e geração a partir da documentação oficial.

## Instalação

```bash
composer require argws/asaas-sdk-php
```

Requisitos:
- PHP 8.1+
- ext-json

## Configuração rápida (sem .env)

```php
<?php

use Asaas\Sdk\AsaasSdk;
use Asaas\Sdk\Config\AsaasConfig;
use Asaas\Sdk\Http\Environment;

$config = new AsaasConfig(
    apiKey: 'SUA_API_KEY',
    environment: Environment::Sandbox, // ou Environment::Production
    appName: 'MinhaApp/1.0',
    timeout: 30.0,
    connectTimeout: 10.0
);

$asaas = new AsaasSdk($config);
```

## Status/Healthcheck (resumo)

Você pode verificar status da API com uma chamada leve (ex.: `listPayments` com `limit=1`). Veja o guia completo em **docs/14-status-healthcheck-resiliencia.md**.

## Índice da documentação

- [Introdução](docs/00-introducao.md)
- [Instalação](docs/01-instalacao.md)
- [Configuração](docs/02-configuracao.md)
- [Quickstart](docs/03-quickstart.md)
- [Sandbox vs Production](docs/04-ambientes-sandbox-production.md)
- [Serviços e endpoints](docs/05-servicos-e-endpoints.md)
- [Exceções e erros](docs/06-exceptions-e-erros.md)
- [Paginação, filtros e ordenação](docs/07-paginacao-filtros-ordenacao.md)
- [Webhooks](docs/08-webhooks.md)
- [Upload/Download de arquivos](docs/09-upload-download-arquivos.md)
- [Multi-tenant](docs/10-multitenancy.md)
- [Exemplos Laravel](docs/11-exemplos-laravel.md)
- [Exemplos CodeEngine](docs/12-exemplos-codeengine.md)
- [Geração OpenAPI e paridade](docs/13-geracao-openapi-e-paridade.md)
- [Status/Healthcheck/Resiliência](docs/14-status-healthcheck-resiliencia.md)
- [Perfex CRM (exemplos)](docs/15-exemplos-perfex-crm.md)
- [Playground (referência)](docs/16-playground-referencia.md)
- [Referência de endpoints (gerada do código)](docs/99-reference-endpoints.md)

## Aviso legal

Este projeto **não é oficial** e **não é afiliado** ao Asaas.  
Asaas é marca registrada de seus respectivos proprietários.

## Playground

https://playground-asaas-sdk.argws.com.br
