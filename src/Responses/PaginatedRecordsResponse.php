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
     *     fields: array{id: positive-int, label: non-empty-string, type: 'text'|'numeric'|'timestamp'|'date'|'timeofday'}[],
     *     metadata: array{
     *         totalRecords: non-negative-int,
     *         numRecords: non-negative-int,
     *         numFields: non-negative-int,
     *         skip: non-negative-int,
     *     },
     * }
     */
    protected array $metadata;

    /**
     * @return iterable<array<non-empty-string|positive-int, mixed>>
     */
    public function getData(): iterable
    {
        $fields = $this->metadata['fields'];

        foreach (parent::getData() as $record) {
            $parsedData = [];

            foreach ($record as $key => $value) {
                $field = $fields[array_search($key, array_column($fields, 'id'), true)];
                $value = QuickbaseUtils::parseField($value, $field['type']);

                $parsedData[$key] = $value;
                $parsedData[$field['label']] = $value;
            }

            yield $parsedData;
        }
    }

    /**
     * @return non-negative-int
     */
    public function getTotalRecords(): int
    {
        return $this->metadata['metadata']['totalRecords'];
    }

    /**
     * @return non-negative-int
     */
    public function getNumRecords(): int
    {
        return $this->metadata['metadata']['numRecords'];
    }

    /**
     * @return non-negative-int
     */
    public function getNumFields(): int
    {
        return $this->metadata['metadata']['numFields'];
    }

    /**
     * @return non-negative-int
     */
    public function getSkip(): int
    {
        return $this->metadata['metadata']['skip'];
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

    protected static function getPointers(): array
    {
        return ['/fields', '/metadata'];
    }
}
