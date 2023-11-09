<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

use Webmozart\Assert\Assert;

final class UpsertRecordsRequest implements \JsonSerializable
{
    /**
     * @var array{
     *     to: non-empty-string,
     *     data?: non-empty-list<array<positive-int, array{value: mixed}>>,
     *     mergeFieldId?: positive-int,
     *     fieldsToReturn?: positive-int[],
     * }
     */
    private array $data;

    /**
     * @param non-empty-string $table
     */
    public function __construct(string $table)
    {
        Assert::stringNotEmpty($table);

        $this->data = [
            'to' => $table,
        ];
    }

    /**
     * @param non-empty-string $table
     */
    public function withTo(string $table): self
    {
        Assert::stringNotEmpty($table);

        $clone = clone $this;
        $clone->data['to'] = $table;

        return $clone;
    }

    /**
     * @param non-empty-list<array<positive-int, mixed>> $data
     */
    public function withData(array $data): self
    {
        Assert::isList($data);
        foreach ($data as $record) {
            Assert::isMap($record);

            foreach ($record as $fieldId => $value) {
                Assert::positiveInteger($fieldId);

                $record[$fieldId] = ['value' => $value];
            }
        }

        $clone = clone $this;
        /** @var non-empty-list<array<positive-int, array{value: mixed}>> $data */
        $clone->data['data'] = $data;

        return $clone;
    }

    /**
     * @param positive-int $fieldId
     */
    public function withMergeFieldId(int $fieldId): self
    {
        Assert::positiveInteger($fieldId);

        $clone = clone $this;
        $clone->data['mergeFieldId'] = $fieldId;

        return $this;
    }

    /**
     * @param positive-int[] $fields
     */
    public function withFieldsToReturn(array $fields): self
    {
        Assert::isList($fields);
        foreach ($fields as $fieldId) {
            Assert::positiveInteger($fieldId);
        }

        $clone = clone $this;
        $clone->data['fieldsToReturn'] = $fields;

        return $this;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }
}