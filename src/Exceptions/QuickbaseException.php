<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Exceptions;

use Psr\Http\Message\ResponseInterface;

class QuickbaseException extends \Exception
{
    public static function fromResponse(ResponseInterface $response): self
    {
        try {
            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            return new static(
                sprintf('%s: %s', $data['message'], $data['description'] ?? 'No details'),
                $response->getStatusCode(),
            );
        } catch (\JsonException $e) {
            return new static('Unknown error', $response->getStatusCode(), $e);
        }
    }
}
