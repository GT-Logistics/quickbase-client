<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Response;

use Psr\Http\Message\ResponseInterface;

final class PaginatedResponse
{
    /**
     * @var array{
     *     data: array<string, array{value: mixed}>[],
     *     fields: array{id: int, label: string, type: string}[],
     *     metadata: array{
     *         totalRecords: int,
     *         numRecords: int,
     *         numFields: int,
     *         skip: int,
     *     },
     * }
     */
    private $data;

    public function __construct(ResponseInterface $response)
    {
        $this->data = json_decode((string) $response->getBody(), true);
    }

    /**
     * @return array<string, array{value: mixed}>[]
     */
    public function getData(): array
    {
        return $this->data['data'];
    }

    /**
     * @return array{id: int, label: string, type: string}[]
     */
    public function getFields(): array
    {
        return $this->data['fields'];
    }

    public function getTotalRecords(): int
    {
        return $this->data['metadata']['totalRecords'];
    }

    public function getNumRecords(): int
    {
        return $this->data['metadata']['numRecords'];
    }

    public function getNumFields(): int
    {
        return $this->data['metadata']['numFields'];
    }

    public function getSkip(): int
    {
        return $this->data['metadata']['skip'];
    }

    public function getNext(): int
    {
        return $this->getSkip() + $this->getNumRecords();
    }

    public function hasNext(): bool
    {
        return $this->getTotalRecords() > $this->getNext();
    }
}
