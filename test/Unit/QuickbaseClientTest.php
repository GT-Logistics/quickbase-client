<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Test\Unit;

use Gtlogistics\QuickbaseClient\Exceptions\MultipleRecordsFoundException;
use Gtlogistics\QuickbaseClient\Exceptions\QuickbaseException;
use Gtlogistics\QuickbaseClient\Query;
use Gtlogistics\QuickbaseClient\QuickbaseClient;
use Gtlogistics\QuickbaseClient\Requests\DeleteRecordsRequest;
use Gtlogistics\QuickbaseClient\Requests\FindRecordRequest;
use Gtlogistics\QuickbaseClient\Requests\QueryRecordsRequest;
use Gtlogistics\QuickbaseClient\Requests\RunReportRequest;
use Gtlogistics\QuickbaseClient\Requests\UpsertRecordsRequest;
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

    public function testQueryEmptyRecords(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('query-records/empty.json')),
        ]);

        $records = IterableUtils::toArray($client->queryRecords(
            (new QueryRecordsRequest('abcdefghi'))
                ->withSelect([6, 7, 8])
                ->withWhere((new Query())->range(8, 'today'))
        ));
        $this->assertCount(0, $records);
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
        $this->assertSame('John Doe', $record1[6]);
        $this->assertSame(10, $record1[7]);
        $this->assertSame('2019-12-18T08:00:00+00:00', $record1[8]->format(\DateTimeInterface::ATOM));

        $record2 = $records[1];
        $this->assertSame('Jane Doe', $record2[6]);
        $this->assertSame(5, $record2[7]);
        $this->assertSame('2019-12-18T09:00:00+00:00', $record2[8]->format(\DateTimeInterface::ATOM));

        $record3 = $records[2];
        $this->assertSame('Andre Harris', $record3[6]);
        $this->assertSame(7, $record3[7]);
        $this->assertSame('2019-12-18T10:00:00+00:00', $record3[8]->format(\DateTimeInterface::ATOM));
    }

    public function testBigQueryRecords(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('query-records/50-mb.json')),
        ]);

        $records = IterableUtils::toArray($client->queryRecords(
            (new QueryRecordsRequest('abcdefghi'))
                ->withSelect([6, 7, 8])
                ->withWhere((new Query())->range(8, 'today'))
        ));
        $this->assertCount(315_835, $records);
    }

    public function testFindEmptyRecord(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('find-record/empty-record.json')),
        ]);

        $record = $client->findRecord(new FindRecordRequest('abcdefghi', 100));
        $this->assertNull($record);
    }

    public function testFindRecord(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('find-record/one-record.json')),
        ]);

        $record = $client->findRecord(new FindRecordRequest('abcdefghi', 100));
        $this->assertSame('John Doe', $record[6]);
        $this->assertSame(10, $record[7]);
        $this->assertSame('2019-12-18T08:00:00+00:00', $record[8]->format(\DateTimeInterface::ATOM));
    }

    public function testFindRecordWithMultiple(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('find-record/multiple-records.json')),
        ]);

        $this->expectException(MultipleRecordsFoundException::class);
        $client->findRecord(new FindRecordRequest('abcdefghi', 100, 10));
    }

    public function testUpsertRecords(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('upsert-records/multiple-records.json')),
        ]);

        $records = IterableUtils::toArray($client->upsertRecords(
            (new UpsertRecordsRequest('abcdefghi'))
                ->withData([
                    [
                        3 => 1,
                        6 => 'Updating this record',
                        7 => 10,
                        8 => new \DateTimeImmutable('2019-12-18T08:00:00Z'),
                    ],
                    [
                        6 => 'This is my text',
                        7 => 15,
                        8 => new \DateTimeImmutable('2019-12-19T08:00:00Z'),
                    ],
                    [
                        6 => 'This is my other text',
                        7 => 20,
                        8 => new \DateTimeImmutable('2019-12-20T08:00:00.000Z'),
                    ],
                ])
                ->withFieldsToReturn([6, 7, 8])
        ));
        $this->assertCount(3, $records);

        $record1 = $records[0];
        $this->assertSame(1, $record1[3]);
        $this->assertSame('Updating this record', $record1[6]);
        $this->assertSame(10, $record1[7]);
        $this->assertSame('2019-12-18T08:00:00.000Z', $record1[8]);

        $record2 = $records[1];
        $this->assertSame(11, $record2[3]);
        $this->assertSame('This is my text', $record2[6]);
        $this->assertSame(15, $record2[7]);
        $this->assertSame('2019-12-19T08:00:00.000Z', $record2[8]);

        $record3 = $records[2];
        $this->assertSame(12, $record3[3]);
        $this->assertSame('This is my other text', $record3[6]);
        $this->assertSame(20, $record3[7]);
        $this->assertSame('2019-12-20T08:00:00.000Z', $record3[8]);
    }

    public function testDeleteRecords(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('delete-records/multiple-records.json')),
        ]);

        $numberDeleted = $client->deleteRecords(new DeleteRecordsRequest('abcdefghi', (new Query())->contains(6, 'test')));
        $this->assertEquals(5, $numberDeleted);
    }

    public function testRunEmptyReport(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('run-report/empty.json')),
        ]);

        $records = IterableUtils::toArray($client->runReport(
            new RunReportRequest('10', 'abcdefghi')
        ));
        $this->assertCount(0, $records);
    }

    public function testRunReport(): void
    {
        $client = $this->mockQuickbaseClient([
            new MockResponse($this->loadFixture('run-report/page-1.json')),
            new MockResponse($this->loadFixture('run-report/page-2.json')),
            new MockResponse($this->loadFixture('run-report/page-3.json')),
        ]);

        $records = IterableUtils::toArray($client->runReport(
            new RunReportRequest('10', 'abcdefghi')
        ));
        $this->assertCount(3, $records);

        $record1 = $records[0];
        $this->assertSame('John Doe', $record1[6]);
        $this->assertSame(10, $record1[7]);
        $this->assertSame('2019-12-18T08:00:00+00:00', $record1[8]->format(\DateTimeInterface::ATOM));

        $record2 = $records[1];
        $this->assertSame('Jane Doe', $record2[6]);
        $this->assertSame(5, $record2[7]);
        $this->assertSame('2019-12-18T09:00:00+00:00', $record2[8]->format(\DateTimeInterface::ATOM));

        $record3 = $records[2];
        $this->assertSame('Andre Harris', $record3[6]);
        $this->assertSame(7, $record3[7]);
        $this->assertSame('2019-12-18T10:00:00+00:00', $record3[8]->format(\DateTimeInterface::ATOM));
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
