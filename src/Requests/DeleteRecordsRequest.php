<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Requests;

use Gtlogistics\QuickbaseClient\Query;
use Webmozart\Assert\Assert;
use function _PHPStan_39fe102d2\RingCentral\Psr7\str;

final class DeleteRecordsRequest implements \JsonSerializable
{
    /**
     * @var array{
     *     from: non-empty-string,
     *     where: non-empty-string,
     * }
     */
    private array $data = [];

    /**
     * @param non-empty-string $table
     * @param non-empty-string|Query $where
     */
    public function __construct(string $table, $where)
    {
        $this->setFrom($table);
        $this->setWhere($where);
    }

    /**
     * @param non-empty-string $table
     */
    public function withFrom(string $table): self
    {
        $clone = clone $this;
        $clone->setFrom($table);

        return $clone;
    }

    /**
     * @param non-empty-string|Query $where
     */
    public function withWhere($where): self
    {
        $clone = clone $this;
        $clone->setWhere($where);

        return $clone;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }

    private function setFrom(string $from): void
    {
        Assert::stringNotEmpty($from);

        $this->data['from'] = $from;
    }

    /**
     * @param string|Query $where
     */
    private function setWhere($where): void
    {
        if ($where instanceof Query) {
            $where = (string) $where;
        }

        Assert::stringNotEmpty($where);

        $this->data['where'] = $where;
    }
}
