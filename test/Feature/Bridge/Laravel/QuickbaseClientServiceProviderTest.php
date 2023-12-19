<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Test\Feature\Bridge\Laravel;

use Gtlogistics\QuickbaseClient\Bridge\Laravel\QuickbaseClientServiceProvider;
use Gtlogistics\QuickbaseClient\QuickbaseClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Orchestra\Testbench\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class QuickbaseClientServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $psr17Factory = new Psr17Factory();
            $client = new Psr18Client(new MockHttpClient());

            $this->instance(ClientInterface::class, $client);
            $this->instance(RequestFactoryInterface::class, $psr17Factory);
            $this->instance(UriFactoryInterface::class, $psr17Factory);
            $this->instance(StreamFactoryInterface::class, $psr17Factory);
        });

        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            QuickbaseClientServiceProvider::class,
        ];
    }

    public function testRegister(): void
    {
        self::assertEquals('test', config('quickbase.token'));
        self::assertEquals('https://example.net', config('quickbase.realm'));
        self::assertEquals('https://example.com', config('quickbase.base_uri'));

        $quickbaseClient = $this->app->make(QuickbaseClient::class);
        self::assertInstanceOf(QuickbaseClient::class, $quickbaseClient);
    }
}
