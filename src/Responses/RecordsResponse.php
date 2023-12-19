<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Responses;

use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * @internal
 */
class RecordsResponse implements \Gtlogistics\QuickbaseClient\Responses\ResponseInterface
{
    /**
     * @var array{
     *     data: array<positive-int, array{value: mixed}>[],
     * }
     */
    protected array $data;

    public function __construct(HttpResponseInterface $response)
    {
        $this->data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<positive-int, array{value: mixed}>[]
     */
    public function getData(): array
    {
        return $this->data['data'];
    }
}
