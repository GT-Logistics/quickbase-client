<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Responses;

/**
 * @internal
 */
class RecordsResponse extends AbstractResponse
{
    /**
     * @var array{
     *     data: array<positive-int, array{value: mixed}>[],
     * }
     */
    protected array $data;

    /**
     * @return array<positive-int, mixed>[]
     */
    public function getData(): array
    {
        return array_map(static fn ($record) => array_map(static fn (array $item) => $item['value'], $record), $this->data['data']);
    }
}
