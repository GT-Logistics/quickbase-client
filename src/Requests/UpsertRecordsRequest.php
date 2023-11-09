<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

final class UpsertRecordsRequest implements \JsonSerializable
{
    private array $data;

    public function __construct(string $table)
    {
        $this->data = [
            'to' => $table,
        ];
    }

    public function withTo(string $table): self
    {
        $clone = clone $this;
        $clone->data['to'] = $table;

        return $clone;
    }

    public function withData(array $data): self
    {
        foreach ($data as $record) {
            if (array_is_list($record)) {
                throw new \InvalidArgumentException('Data records must not be an array');
            }

            foreach ($record as $fieldId => $value) {
                if (!is_numeric($fieldId)) {
                    throw new \InvalidArgumentException(sprintf('Data key must be an ID, %s given', $fieldId));
                }
                if ($fieldId <= 0) {
                    throw new \InvalidArgumentException(sprintf('Data key must be a positive integer, %d given', $fieldId));
                }
                if (!is_array($value) || !array_key_exists('value', $value)) {
                    throw new \InvalidArgumentException(sprintf('Data value must be an array with the key "value", %s given', $value));
                }
            }
        }

        $clone = clone $this;
        $clone->data['data'] = $data;

        return $clone;
    }

    public function withMergeFieldId(int $fieldId): self
    {
        if ($fieldId <= 0) {
            throw new \InvalidArgumentException('Merge field id must be a positive integer, %d given', $fieldId);
        }

        $clone = clone $this;
        $clone->data['mergeFieldId'] = $fieldId;

        return $this;
    }

    public function withFieldsToReturn(array $fields): self
    {
        foreach ($fields as $fieldId) {
            if (!is_numeric($fieldId)) {
                throw new \InvalidArgumentException(sprintf('Fields to return must be an array of integers, %s given', $fieldId));
            }
            if ($fieldId <= 0) {
                throw new \InvalidArgumentException(sprintf('Fields to return must be an array of positive integer, %d given', $fieldId));
            }
        }

        $clone = clone $this;
        $clone->data['fieldsToReturn'] = $fields;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
