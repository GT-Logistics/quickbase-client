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

    public static function withQuery(RequestInterface $request, array $query): RequestInterface
    {
        $url = $request->getUri();
        parse_str($url->getQuery(), $oldQuery);

        $query = array_merge($oldQuery, $query);
        $url = $url->withQuery(http_build_query($query));

        return $request->withUri($url);
    }
}
