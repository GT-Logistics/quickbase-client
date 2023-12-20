<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Test\Unit;

use Gtlogistics\QuickbaseClient\Exceptions\MultipleRecordsFoundException;
use Gtlogistics\QuickbaseClient\Exceptions\QuickbaseException;
use Gtlogistics\QuickbaseClient\Query;
use Gtlogistics\QuickbaseClient\QuickbaseClient;
use Gtlogistics\QuickbaseClient\Requests\FindRecordRequest;
use Gtlogistics\QuickbaseClient\Requests\QueryRecordsRequest;
use Gtlogistics\QuickbaseClient\Test\ApiTestCase;
use Gtlogistics\QuickbaseClient\Test\Utils\IterableUtils;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class QuickbaseClientTest extends ApiTestCase
{
    /**
     * @testWith ["400-error.json", "Bad request: Required header 'QB-Realm-Hostname' not specified", 400]
     *           ["403-error.json", "Access denied: User token is invalid", 403]
     */
    public function testQuickbaseException(string $fixture, string $expectedMessage, int $expectedCode): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture($fixture), ['http_code' => $expectedCode]),
        ]);

        $this->expectException(QuickbaseException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->expectExceptionCode($expectedCode);
        // Force to make the request, because of the async nature of the client
        IterableUtils::toArray($client->queryRecords(new QueryRecordsRequest('abcdefghi')));
    }

    public function testQueryRecords(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('query-records/page-1.json')),
            new MockResponse($this->loadFixture('query-records/page-2.json')),
            new MockResponse($this->loadFixture('query-records/page-3.json')),
        ]);

        $records = IterableUtils::toArray($client->queryRecords(
            (new QueryRecordsRequest('abcdefghi'))
                ->withSelect([6, 7, 8])
                ->withWhere((new Query())->range(8, 'today'))
        ));
        $this->assertCount(3, $records);

        $record1 = $records[0];
        $this->assertSame('John Doe', $record1[6]['value']);
        $this->assertSame(10, $record1[7]['value']);
        $this->assertSame('2019-12-18T08:00:00Z', $record1[8]['value']);

        $record2 = $records[1];
        $this->assertSame('Jane Doe', $record2[6]['value']);
        $this->assertSame(5, $record2[7]['value']);
        $this->assertSame('2019-12-18T09:00:00Z', $record2[8]['value']);

        $record3 = $records[2];
        $this->assertSame('Andre Harris', $record3[6]['value']);
        $this->assertSame(7, $record3[7]['value']);
        $this->assertSame('2019-12-18T10:00:00Z', $record3[8]['value']);
    }

    public function testFindRecord(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('find-record/one-record.json')),
        ]);

        $record = $client->findRecord(new FindRecordRequest('abcdefghi', 100));
        $this->assertSame('John Doe', $record[6]['value']);
        $this->assertSame(10, $record[7]['value']);
        $this->assertSame('2019-12-18T08:00:00Z', $record[8]['value']);
    }

    public function testFindRecordWithMultiple(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('find-record/multiple-records.json')),
        ]);

        $this->expectException(MultipleRecordsFoundException::class);
        $client->findRecord(new FindRecordRequest('abcdefghi', 100));
    }

    /**
     * @param callable|callable[]|iterable|null|ResponseInterface|ResponseInterface[] $responseFactory
     */
    private function mockQuickbaseClient($responseFactory): QuickbaseClient
    {
        $psr17Factory = new Psr17Factory();

        return new QuickbaseClient(
            $this->mockClient($responseFactory),
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            'test',
            'https://example.com',
        );
    }
}
