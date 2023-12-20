<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Test;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class ApiTestCase extends TestCase
{
    /**
     * @param callable|callable[]|iterable|null|ResponseInterface|ResponseInterface[] $responseFactory
     */
    public function mockClient($responseFactory): ClientInterface
    {
        return new Psr18Client(new MockHttpClient($responseFactory));
    }

    public function loadFixture(string $path)
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . ltrim($path, '/'));
    }
}
