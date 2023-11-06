<?php

namespace Gtlogistics\QuickbaseClient\Requests;

use Gtlogistics\QuickbaseClient\Query;

final class QueryRequest implements PaginableRequestInterface, \JsonSerializable
{
    /**
     * @var mixed[]
     */
    private array $data;

    public function __construct(string $from)
    {
        $this->data = [
            'from' => $from,
        ];
    }

    public function withFrom(string $table): self
    {
        $clone = clone $this;
        $clone->data['from'] = $table;

        return $clone;
    }

    /**
     * @param int[] $fields
     */
    public function withSelect(array $fields): self
    {
        foreach ($fields as $field) {
            if (!is_int($field)) {
                throw new \RuntimeException(sprintf('Select can only accept integer values, %s given', $field));
            }
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
        $clone = clone $this;
        $clone->data['where'] = (string) $where;

        (new Query())->contains(1, 'true');

        return $clone;
    }

    /**
     * @param array{fieldId: string, order: string}[] $fields
     */
    public function withSortBy(array $fields): self
    {
        foreach ($fields as $field) {
            $fieldId = $field['fieldId'] ?? '';
            $order = $field['order'] ?? '';

            if (!is_int($fieldId)) {
                throw new \RuntimeException(sprintf('SortBy fieldId can only accept integer values, %s given', $fieldId));
            }
            if (!in_array($order, ['ASC', 'DESC'])) {
                throw new \RuntimeException(sprintf('SortBy order only accept "ASC", "DESC", %s given', $order));
            }
        }

        $clone = clone $this;
        $clone->data['sortBy'] = $fields;

        return $clone;
    }

    public function withSkip(int $count): self
    {
        $clone = clone $this;
        $clone->data['options'] ??= [];
        $clone->data['options']['skip'] = $count;

        return $clone;
    }

    public function withTop(int $count): self
    {
        $clone = clone $this;
        $clone->data['options'] ??= [];
        $clone->data['options']['top'] = $count;

        return $clone;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
