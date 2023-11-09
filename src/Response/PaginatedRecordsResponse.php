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
     *     data: array<positive-int, array{value: mixed}>[],
     *     fields: array{id: positive-int, label: non-empty-string, type: non-empty-string}[],
     *     metadata: array{
     *         totalRecords: non-negative-int,
     *         numRecords: non-negative-int,
     *         numFields: non-negative-int,
     *         skip: non-negative-int,
     *     },
     * }
     */
    protected array $data;

    /**
     * @return array{id: positive-int, label: non-empty-string, type: non-empty-string}[]
     */
    public function getFields(): array
    {
        return $this->data['fields'];
    }

    /**
     * @return non-negative-int
     */
    public function getTotalRecords(): int
    {
        return $this->data['metadata']['totalRecords'];
    }

    /**
     * @return non-negative-int
     */
    public function getNumRecords(): int
    {
        return $this->data['metadata']['numRecords'];
    }

    /**
     * @return non-negative-int
     */
    public function getNumFields(): int
    {
        return $this->data['metadata']['numFields'];
    }

    /**
     * @return non-negative-int
     */
    public function getSkip(): int
    {
        return $this->data['metadata']['skip'];
    }

    /**
     * @return non-negative-int
     */
    public function getNext(): int
    {
        return $this->getSkip() + $this->getNumRecords();
    }

    public function hasNext(): bool
    {
        return $this->getTotalRecords() > $this->getNext();
    }
}
