<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Utils;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function Safe\json_encode;

final class RequestUtils
{
    public static function withPayload(RequestInterface $request, StreamFactoryInterface $streamFactory, \JsonSerializable $payload = null): RequestInterface
    {
        if ($payload) {
            return $request->withBody($streamFactory->createStream(json_encode($request)));
        }

        return $request->withBody($streamFactory->createStream());
    }
}