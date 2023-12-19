<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Test\Feature\Bridge\Laravel;

use Gtlogistics\QuickbaseClient\Bridge\Laravel\QuickbaseClientServiceProvider;
use Gtlogistics\QuickbaseClient\QuickbaseClient;
use Orchestra\Testbench\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class QuickbaseClientServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->mock(ClientInterface::class);
            $this->mock(RequestFactoryInterface::class);
            $this->mock(UriFactoryInterface::class);
            $this->mock(StreamFactoryInterface::class);
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
