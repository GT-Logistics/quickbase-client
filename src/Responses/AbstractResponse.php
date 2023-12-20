<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Responses;

use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * @internal
 */
abstract class AbstractResponse implements ResponseInterface
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return static
     */
    public static function fromResponse(HttpResponseInterface $response)
    {
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return new static($data);
    }
}
