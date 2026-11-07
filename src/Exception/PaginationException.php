<?php

declare(strict_types=1);

namespace Asaas\Sdk\Exception;

/** A paginated read could not be completed safely. No response data is logged. */
final class PaginationException extends \RuntimeException
{
    public function __construct(string $message, private int $offset)
    {
        parent::__construct($message);
    }

    /** Offset of the page that was not processed. */
    public function getOffset(): int
    {
        return $this->offset;
    }
}
