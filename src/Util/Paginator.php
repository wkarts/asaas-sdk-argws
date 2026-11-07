<?php

declare(strict_types=1);

namespace Asaas\Sdk\Util;

use Asaas\Sdk\Exception\PaginationException;

/** Optional, lazy pagination for GET operations with the Asaas list envelope. */
final class Paginator
{
    /**
     * The callback must perform a read-only list operation using query: $query.
     * Exceptions from that operation are preserved. A page cap is an error, not
     * successful completion: publish exports only after consuming the generator.
     *
     * @param callable(array<string, mixed>): mixed $fetchPage
     * @param array<string, mixed> $query
     * @return \Generator<int, array<string, mixed>>
     */
    public static function pages(callable $fetchPage, array $query = [], int $maxPages = 1000): \Generator
    {
        $limit = $query['limit'] ?? 100;
        $offset = $query['offset'] ?? 0;
        if (!is_int($limit) || $limit < 1 || $limit > 100 || !is_int($offset) || $offset < 0 || $maxPages < 1) {
            throw new \InvalidArgumentException('Pagination requires integer limit 1..100, offset >= 0 and maxPages >= 1.');
        }

        $query['limit'] = $limit;
        $previousFingerprint = null;

        for ($pageNumber = 0; ; ++$pageNumber) {
            if ($pageNumber >= $maxPages) {
                throw new PaginationException('Page limit reached before the listing was complete.', $offset);
            }

            $query['offset'] = $offset;
            $page = $fetchPage($query);
            if (!is_array($page) || !isset($page['data']) || !is_array($page['data'])
                || !array_is_list($page['data']) || !isset($page['hasMore']) || !is_bool($page['hasMore'])) {
                throw new PaginationException('Invalid list response: data and boolean hasMore are required.', $offset);
            }
            if (isset($page['offset']) && $page['offset'] !== $offset) {
                throw new PaginationException('The response offset differs from the requested page.', $offset);
            }
            $count = count($page['data']);
            $effectiveLimit = $page['limit'] ?? $limit;
            if (!is_int($effectiveLimit) || $effectiveLimit < 1 || $effectiveLimit > $limit
                || $count > $effectiveLimit) {
                throw new PaginationException('Invalid page size in list response.', $offset);
            }
            if ($page['hasMore'] && $page['data'] === []) {
                throw new PaginationException('The API returned an empty page with hasMore enabled.', $offset);
            }
            if (array_key_exists('totalCount', $page)
                && (!is_int($page['totalCount']) || $page['totalCount'] < 0
                    || ($count > 0 && $page['totalCount'] - $offset < $count)
                    || ($page['hasMore'] && $page['totalCount'] - $offset <= $count)
                    || (!$page['hasMore'] && $page['totalCount'] - $offset > $count))) {
                throw new PaginationException('The response count contradicts completion of the listing.', $offset);
            }

            foreach ($page['data'] as $item) {
                if (!is_array($item)) {
                    throw new PaginationException('Invalid resource in list response.', $offset);
                }
            }

            // Keep only one page fingerprint in memory. IDs detect a repeated page
            // even when ancillary values changed between requests.
            $identity = array_map(static fn(array $item): mixed => $item['id'] ?? $item, $page['data']);
            $fingerprint = hash('sha256', serialize($identity));
            if ($page['data'] !== [] && $fingerprint === $previousFingerprint) {
                throw new PaginationException('The API repeated the preceding page; listing is incomplete.', $offset);
            }
            $previousFingerprint = $fingerprint;

            yield $pageNumber => $page;

            if (!$page['hasMore']) {
                return;
            }
            if ($offset > PHP_INT_MAX - $count) {
                throw new PaginationException('Pagination offset exceeds the supported range.', $offset);
            }
            // Offset identifies the next record, not the next requested page:
            // short pages must not skip records even when hasMore remains true.
            $offset += $count;
        }
    }

    /**
     * @param callable(array<string, mixed>): mixed $fetchPage
     * @param array<string, mixed> $query
     * @return \Generator<int, array<string, mixed>>
     */
    public static function items(callable $fetchPage, array $query = [], int $maxPages = 1000): \Generator
    {
        foreach (self::pages($fetchPage, $query, $maxPages) as $page) {
            foreach ($page['data'] as $item) {
                yield $item;
            }
        }
    }
}
