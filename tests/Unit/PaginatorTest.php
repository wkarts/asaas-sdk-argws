<?php

declare(strict_types=1);

namespace Asaas\Sdk\Tests\Unit;

use Asaas\Sdk\Exception\PaginationException;
use Asaas\Sdk\Util\Paginator;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    public function testFetchesEveryPageLazilyAndPreservesFiltersAndUnknownFields(): void
    {
        $queries = [];
        $fetch = static function (array $query) use (&$queries): array {
            $queries[] = $query;
            $offset = $query['offset'];
            return ['data' => [['id' => 'txn_' . $offset, 'type' => 'NEW_TYPE', 'extra' => ['future' => true]]],
                'hasMore' => $offset < 2, 'offset' => $offset, 'limit' => 1];
        };
        $filters = ['startDate' => '2026-10-01', 'finishDate' => '2026-10-31', 'limit' => 1];
        $items = Paginator::items($fetch, $filters);
        self::assertSame([], $queries);
        $items->rewind();
        self::assertCount(1, $queries);
        $result = iterator_to_array($items);
        self::assertSame(['txn_0', 'txn_1', 'txn_2'], array_column($result, 'id'));
        self::assertSame(['future' => true], $result[0]['extra']);
        self::assertSame('NEW_TYPE', $result[0]['type']);
        self::assertSame([0, 1, 2], array_column($queries, 'offset'));
        self::assertSame([$filters + ['offset' => 0], $filters + ['offset' => 1], $filters + ['offset' => 2]], $queries);
        self::assertArrayNotHasKey('offset', $filters);
    }

    public function testUsesResponseLimitWhenTheServerClampsTheRequestedPage(): void
    {
        $offsets = [];
        $pages = iterator_to_array(Paginator::pages(static function (array $query) use (&$offsets): array {
            $offsets[] = $query['offset'];
            return ['data' => [['id' => (string) $query['offset']]], 'hasMore' => $query['offset'] === 5, 'limit' => 1, 'offset' => $query['offset']];
        }, ['offset' => 5, 'limit' => 100]));
        self::assertCount(2, $pages);
        self::assertSame([5, 6], $offsets);
    }

    public function testEmptyTerminalPageIsAValidEmptyListing(): void
    {
        self::assertSame([], iterator_to_array(Paginator::items(static fn(array $query): array => ['data' => [], 'hasMore' => false])));
        self::assertSame([], iterator_to_array(Paginator::items(static fn(array $query): array => ['data' => [], 'hasMore' => false, 'totalCount' => 2], ['offset' => 10])));
    }

    public function testShortPagesAdvanceByTheNumberOfRecordsReturned(): void
    {
        foreach ([[], ['limit' => 100]] as $metadata) {
            $offsets = [];
            $items = iterator_to_array(Paginator::items(static function (array $query) use (&$offsets, $metadata): array {
                $offset = $query['offset'];
                $offsets[] = $offset;
                $ids = $offset === 0 ? ['one', 'two'] : ['three'];
                return ['data' => array_map(static fn(string $id): array => ['id' => $id], $ids),
                    'hasMore' => $offset === 0, 'totalCount' => 3] + $metadata;
            }, ['limit' => 100]));
            self::assertSame([0, 2], $offsets);
            self::assertSame(['one', 'two', 'three'], array_column($items, 'id'));
        }
    }

    public function testTotalCountIsValidatedAgainstTheCurrentOffsetBeforeYielding(): void
    {
        foreach ([
            ['data' => [['id' => 'one'], ['id' => 'two']], 'hasMore' => false, 'totalCount' => 6],
            ['data' => [['id' => 'one']], 'hasMore' => true, 'totalCount' => 6],
            ['data' => [['id' => 'one']], 'hasMore' => true, 'totalCount' => 4],
        ] as $page) {
            $processed = [];
            try {
                foreach (Paginator::items(static fn(array $query): array => $page, ['offset' => 5]) as $item) {
                    $processed[] = $item;
                }
                self::fail('A contradictory total must fail before exposing any records');
            } catch (PaginationException $exception) {
                self::assertSame(5, $exception->getOffset());
                self::assertSame([], $processed);
            }
        }
    }

    public function testPageLimitFailsExplicitlyBeforeAnotherRequest(): void
    {
        $calls = 0;
        $items = Paginator::items(static function (array $query) use (&$calls): array {
            ++$calls;
            return ['data' => [['id' => 'one']], 'hasMore' => true];
        }, ['limit' => 1], 1);
        $items->rewind();
        self::assertSame(['id' => 'one'], $items->current());
        try {
            $items->next();
            self::fail('A capped result cannot be reported as complete');
        } catch (PaginationException $exception) {
            self::assertSame(1, $exception->getOffset());
            self::assertSame(1, $calls);
        }
    }

    public function testRepeatedPageIsRejectedEvenIfStateChanged(): void
    {
        $processed = [];
        try {
            foreach (Paginator::items(static fn(array $query): array => ['data' => [['id' => 'same', 'state' => $query['offset']]], 'hasMore' => true], ['limit' => 1]) as $item) {
                $processed[] = $item;
            }
            self::fail('Repeating the same resource must not create a complete export');
        } catch (PaginationException $exception) {
            self::assertCount(1, $processed);
            self::assertSame(1, $exception->getOffset());
        }
    }

    public function testMalformedResponsesAreNotInterpretedAsSuccessfulEmptyResults(): void
    {
        $invalidPages = [
            null, [], ['data' => []], ['data' => [], 'hasMore' => 'false'],
            ['data' => ['id' => 'object-instead-of-list'], 'hasMore' => false],
            ['data' => [], 'hasMore' => true], ['data' => ['not-an-object'], 'hasMore' => false],
            ['data' => [['id' => 'one']], 'hasMore' => false, 'offset' => 99],
            ['data' => [], 'hasMore' => false, 'limit' => 0],
            ['data' => [], 'hasMore' => false, 'limit' => 101],
            ['data' => [], 'hasMore' => false, 'totalCount' => 1],
            ['data' => [], 'hasMore' => false, 'totalCount' => '0'],
            ['data' => [], 'hasMore' => false, 'totalCount' => -1],
            ['data' => [['id' => 'one'], ['id' => 'two']], 'hasMore' => false, 'totalCount' => 1],
            ['data' => [['id' => 'one']], 'hasMore' => true, 'totalCount' => 1],
        ];
        foreach ($invalidPages as $page) {
            try {
                iterator_to_array(Paginator::pages(static fn(array $query): mixed => $page));
                self::fail('Invalid page must throw');
            } catch (PaginationException $exception) {
                self::assertSame(0, $exception->getOffset());
            }
        }
    }

    public function testInvalidPagingArgumentsFailBeforeAnyRequest(): void
    {
        foreach ([['limit' => 0], ['limit' => 101], ['limit' => '10'], ['offset' => -1], ['offset' => '0']] as $query) {
            $called = false;
            try {
                iterator_to_array(Paginator::pages(static function (array $q) use (&$called): array {
                    $called = true;
                    return [];
                }, $query));
                self::fail('Invalid arguments must fail before HTTP');
            } catch (\InvalidArgumentException $exception) {
                self::assertFalse($called);
            }
        }
        $this->expectException(\InvalidArgumentException::class);
        iterator_to_array(Paginator::pages(static fn(array $query): array => [], maxPages: 0));
    }

    public function testOriginalReadErrorsRemainVisible(): void
    {
        $failure = new \RuntimeException('Synthetic API failure');
        try {
            iterator_to_array(Paginator::items(static function (array $query) use ($failure): never {
                throw $failure;
            }));
            self::fail('The original failure must propagate');
        } catch (\RuntimeException $exception) {
            self::assertSame($failure, $exception);
        }
    }
}
