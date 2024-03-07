<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Responses;

use Gtlogistics\QuickbaseClient\Utils\QuickbaseUtils;

/**
 * @internal
 */
final class PaginatedRecordsResponse extends RecordsResponse
{
    /**
     * @var array{
     *     data: array<positive-int, array{value: mixed}>[],
     *     fields: array{id: positive-int, label: non-empty-string, type: 'text'|'numeric'|'timestamp'|'date'|'timeofday'}[],
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
     * @return array<positive-int, mixed>[]
     */
    public function getData(): array
    {
        $fields = $this->data['fields'];

        return array_map(static function (array $record) use ($fields) {
            $parsedData = [];

            foreach ($record as $key => $value) {
                $field = $fields[array_search($key, array_column($fields, 'id'), true)];
                $parsedData[$key] = QuickbaseUtils::parseField($value, $field['type']);
            }

            return $parsedData;
        }, parent::getData());
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
