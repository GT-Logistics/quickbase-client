<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Utils;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function Safe\json_encode;

/**
 * @internal
 */
final class RequestUtils
{
    public static function withPayload(RequestInterface $request, StreamFactoryInterface $streamFactory, \JsonSerializable $payload = null): RequestInterface
    {
        if ($payload) {
            return $request
                ->withHeader('content-type', 'application/json')
                ->withBody($streamFactory->createStream(json_encode($payload)));
        }

        return $request->withBody($streamFactory->createStream());
    }
}
