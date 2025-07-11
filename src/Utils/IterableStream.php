<?php
/*
 * Copyright (c) 2025 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Utils;

use Psr\Http\Message\StreamInterface;

class IterableStream implements \Iterator
{
    private StreamInterface $stream;

    private string $current;

    private int $index = -1;

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function current(): string
    {
        return $this->current;
    }

    public function next(): void
    {
        $this->current = $this->stream->read(8 * 1024 /* 8 KiB */);
        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return !$this->stream->eof();
    }

    public function rewind(): void
    {
        $this->stream->rewind();
        $this->current = $this->stream->read(8 * 1024 /* 8 KiB */);
        $this->index = 0;
    }
}
