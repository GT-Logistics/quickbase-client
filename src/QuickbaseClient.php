<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient;

use Gtlogistics\QuickbaseClient\Requests\PaginableRequestInterface;
use Gtlogistics\QuickbaseClient\Requests\QueryRecordsRequest;
use Gtlogistics\QuickbaseClient\Requests\UpsertRecordsRequest;
use Gtlogistics\QuickbaseClient\Response\PaginatedRecordsResponse;
use Gtlogistics\QuickbaseClient\Response\RecordsResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function Safe\json_encode;

final class QuickbaseClient
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    private string $baseUri;

    private string $token;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $token,
        string $baseUri = 'https://api.quickbase.com'
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->baseUri = $baseUri;
        $this->token = $token;
    }

    /**
     * @api
     *
     * @return iterable<positive-int, array{value: mixed}>[]
     */
    public function upsertRecords(UpsertRecordsRequest $request): iterable
    {
        $httpRequest = $this->makeRequest('POST', '/v1/records');

        return $this->recordsResponse($httpRequest, $request);
    }

    /**
     * @api
     *
     * @return iterable<positive-int, array{value: mixed}>[]
     */
    public function queryRecords(QueryRecordsRequest $request): iterable
    {
        $httpRequest = $this->makeRequest('POST', '/v1/records/query');

        return $this->paginatedRecordsResponse($httpRequest, $request);
    }

    private function makeRequest(string $method, string $uri): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $this->baseUri . $uri);

        return $request
            ->withHeader('QB-Realm-Hostname', 'https://gtglobal.quickbase.com')
            ->withHeader('Authorization', "QB-USER-TOKEN $this->token");
    }

    /**
     * @return iterable<positive-int, array{value: mixed}>[]
     */
    private function recordsResponse(RequestInterface $httpRequest, \JsonSerializable $request): iterable
    {
        $httpRequest = $httpRequest->withBody($this->streamFactory->createStream(json_encode($request)));

        $httpResponse = $this->client->sendRequest($httpRequest);
        $response = new RecordsResponse($httpResponse);

        return $response->getData();
    }

    /**
     * @return iterable<positive-int, array{value: mixed}>[]
     */
    private function paginatedRecordsResponse(RequestInterface $httpRequest, PaginableRequestInterface $request): iterable
    {
        while (true) {
            $httpRequest = $httpRequest->withBody($this->streamFactory->createStream(json_encode($request)));

            $httpResponse = $this->client->sendRequest($httpRequest);
            $response = new PaginatedRecordsResponse($httpResponse);

            foreach ($response->getData() as $record) {
                yield $record;
            }

            if (!$response->hasNext()) {
                break;
            }

            $request = $request->withSkip($response->getNext());
        }
    }
}
