<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class RecordsResponse
{
    /**
     * @var array{
     *     data: array<string, array{value: mixed}>[],
     * }
     */
    protected array $data;

    public function __construct(ResponseInterface $response)
    {
        $this->data = json_decode((string) $response->getBody(), true);
    }

    /**
     * @return array<string, array{value: mixed}>[]
     */
    public function getData(): array
    {
        return $this->data['data'];
    }
}
