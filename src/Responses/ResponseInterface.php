<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Responses;

use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * @internal
 */
interface ResponseInterface
{
    public static function fromResponse(HttpResponseInterface $response);
}
