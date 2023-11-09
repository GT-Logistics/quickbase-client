<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Response;

/**
 * @internal
 */
final class PaginatedRecordsResponse extends RecordsResponse
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
    protected array $data;

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
