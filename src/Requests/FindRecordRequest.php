<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

use Gtlogistics\QuickbaseClient\Query;
use Webmozart\Assert\Assert;

final class FindRecordRequest implements \JsonSerializable
{
    /**
     * @var array{
     *     from: non-empty-string,
     *     select?: positive-int[],
     * }
     */
    private array $data;

    private int $fieldId;

    /**
     * @var int|float|non-empty-string
     */
    private $id;

    /**
     * @param non-empty-string $table
     * @param int|float|non-empty-string $id
     * @param positive-int $fieldId
     */
    public function __construct(string $table, $id, int $fieldId = 3)
    {
        $this->validateFrom($table);
        $this->validateFieldId($fieldId);
        $this->validateId($id);

        $this->data = [
            'from' => $table,
        ];
        $this->fieldId = $fieldId;
        $this->id = $id;
    }

    /**
     * @param non-empty-string $table
     */
    public function withFrom(string $table): self
    {
        $this->validateFrom($table);

        $clone = clone $this;
        $clone->data['from'] = $table;

        return $clone;
    }

    /**
     * @param positive-int[] $fields
     */
    public function withSelect(array $fields): self
    {
        $this->validateSelect($fields);

        $clone = clone $this;
        $clone->data['select'] = $fields;

        return $clone;
    }

    /**
     * @param int|non-empty-string $id
     */
    public function withId($id): self
    {
        $this->validateId($id);

        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withFieldId(int $fieldId): self
    {
        $this->validateFieldId($fieldId);

        $clone = clone $this;
        $clone->fieldId = $fieldId;

        return $clone;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = $this->data;
        $data['where'] = (string) (new Query())->equals($this->fieldId, $this->id);

        return $data;
    }

    private function validateFrom(string $from): void
    {
        Assert::stringNotEmpty($from);
    }

    /**
     * @param positive-int[] $fields
     */
    private function validateSelect(array $fields): void
    {
        Assert::isList($fields);
        foreach ($fields as $fieldId) {
            Assert::positiveInteger($fieldId);
        }
    }

    private function validateFieldId(int $fieldId): void
    {
        Assert::positiveInteger($fieldId);
    }

    /**
     * @param int|float|non-empty-string $id
     */
    private function validateId($id): void
    {
        if (is_string($id)) {
            Assert::stringNotEmpty($id);

            return;
        }
        if (is_int($id) || is_float($id)) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Expected a int, float or string. Got %s', $id));
    }
}
