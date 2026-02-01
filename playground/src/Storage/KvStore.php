<?php

declare(strict_types=1);

namespace Playground\Storage;

final class KvStore
{
    public function __construct(private Sqlite $sqlite) {}

    public function get(string $key): ?string
    {
        $stmt = $this->sqlite->pdo()->prepare('SELECT value FROM kv_store WHERE key = :key');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();

        return $value === false ? null : (string) $value;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->sqlite->pdo()->prepare(
            'INSERT INTO kv_store (key, value) VALUES (:key, :value) '
            . 'ON CONFLICT(key) DO UPDATE SET value = excluded.value'
        );
        $stmt->execute(['key' => $key, 'value' => $value]);
    }
}
