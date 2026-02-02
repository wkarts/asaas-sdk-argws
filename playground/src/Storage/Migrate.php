<?php

declare(strict_types=1);

namespace Playground\Storage;

final class Migrate
{
    public static function run(string $basePath): void
    {
        $storagePath = $basePath . '/storage';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0o777, true);
        }

        $dbPath = $storagePath . '/database.sqlite';
        if (!file_exists($dbPath)) {
            touch($dbPath);
        }

        $sqlite = new Sqlite($dbPath);
        $pdo = $sqlite->pdo();

        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                created_at TEXT NOT NULL,
                action TEXT NOT NULL,
                params_json TEXT,
                duration_ms INTEGER,
                success INTEGER,
                http_status INTEGER,
                error_message TEXT,
                response_excerpt TEXT
            );
            SQL);

        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS webhooks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                received_at TEXT NOT NULL,
                headers_json TEXT,
                payload_json TEXT,
                validated_token INTEGER,
                ip TEXT,
                user_agent TEXT,
                processed_at TEXT
            );
            SQL);

        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS kv_store (
                key TEXT PRIMARY KEY,
                value TEXT
            );
            SQL);
    }
}
