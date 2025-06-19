<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

use Gtlogistics\QuickbaseClient\Query;
use Webmozart\Assert\Assert;

final class QueryRecordsRequest implements PaginableRequestInterface, \JsonSerializable
{
    /**
     * @var array{
     *     from: non-empty-string,
     *     select?: positive-int[],
     *     where?: non-empty-string,
     *     sortBy?: array{
     *         fieldId: positive-int,
     *         order: 'ASC'|'DESC',
     *     }[],
     *     groupBy?: array{
     *         fieldId: positive-int,
     *         grouping: 'equal-values',
     *     }[],
     *     options?: array{
     *         skip?: non-negative-int,
     *         top?: non-negative-int,
     *         compareWithAppLocalTime?: boolean,
     *     },
     * }
     */
    private array $data;

    /**
     * @param non-empty-string $from
     */
    public function __construct(string $from)
    {
        Assert::stringNotEmpty($from);

        $this->data = [
            'from' => $from,
        ];
    }

    /**
     * @param non-empty-string $table
     */
    public function withFrom(string $table): self
    {
        Assert::stringNotEmpty($table);

        $clone = clone $this;
        $clone->data['from'] = $table;

        return $clone;
    }

    /**
     * @param positive-int[] $fields
     */
    public function withSelect(array $fields): self
    {
        Assert::isList($fields);
        foreach ($fields as $fieldId) {
            Assert::positiveInteger($fieldId);
        }

        $clone = clone $this;
        $clone->data['select'] = $fields;

        return $clone;
    }

    /**
     * @param string|Query $where
     */
    public function withWhere($where): self
    {
        if ($where instanceof Query) {
            $where = (string) $where;
        }

        $clone = clone $this;
        if (trim($where) === '') {
            unset($clone->data['where']);

            return $clone;
        }

        $clone->data['where'] = $where;

        return $clone;
    }

    /**
     * @param array{
     *     fieldId: positive-int,
     *     order: 'ASC'|'DESC',
     * }[] $fields
     */
    public function withSortBy(array $fields): self
    {
        Assert::isList($fields);
        foreach ($fields as $field) {
            Assert::isMap($field);
            Assert::keyExists($field, 'fieldId');
            Assert::keyExists($field, 'order');

            $fieldId = $field['fieldId'];
            $order = $field['order'];

            Assert::positiveInteger($fieldId);
            Assert::inArray($order, ['ASC', 'DESC']);
        }

        $clone = clone $this;
        $clone->data['sortBy'] = $fields;

        return $clone;
    }

    /**
     * @param non-negative-int $count
     */
    public function withSkip(int $count): self
    {
        Assert::natural($count);

        $clone = clone $this;
        $clone->data['options'] ??= [];
        $clone->data['options']['skip'] = $count;

        return $clone;
    }

    /**
     * @param non-negative-int $count
     */
    public function withTop(int $count): self
    {
        Assert::natural($count);

        $clone = clone $this;
        $clone->data['options'] ??= [];
        $clone->data['options']['top'] = $count;

        return $clone;
    }

    public function getPaginationType(): string
    {
        return self::PAGINATION_IN_PAYLOAD;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }
}
