<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Responses;

use Gtlogistics\QuickbaseClient\Utils\IterableStream;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

/**
 * @internal
 */
class RecordsResponse implements ResponseInterface
{
    /** @var iterable<positive-int, array{value: mixed}>  */
    protected iterable $data;

    /** @var array<array-key, mixed> */
    protected array $metadata;

    public function __construct(iterable $data, array $metadata)
    {
        $this->data = $data;
        $this->metadata = $metadata;
    }

    /**
     * @return iterable<array<positive-int, mixed>>
     */
    public function getData(): iterable
    {
        foreach ($this->data as $record) {
            yield array_map(static fn (array $item) => $item['value'], $record);
        }
    }

    /**
     * @return static
     */
    public static function fromResponse(HttpResponseInterface $response)
    {
        $stream = new IterableStream($response->getBody());
        $data = Items::fromIterable($stream, [
            'decoder' => new ExtJsonDecoder(true),
            'pointer' => ['/data'],
        ]);
        $metadata = Items::fromIterable($stream, [
            'decoder' => new ExtJsonDecoder(true),
            'pointer' => static::getPointers(),
        ]);

        $parsedMetadata = [];
        foreach ($metadata as $key => $item) {
            $pointer = ltrim($metadata->getCurrentJsonPointer(), '/');
            $parsedMetadata[$pointer][$key] = $item;
        }

        return new static($data, $parsedMetadata);
    }

    /**
     * @return string[]
     */
    protected static function getPointers(): array
    {
        return [];
    }
}
