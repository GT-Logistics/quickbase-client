<?php

namespace Gtlogistics\QuickbaseClient;

use Gtlogistics\QuickbaseClient\Requests\PaginableRequestInterface;
use Gtlogistics\QuickbaseClient\Requests\QueryRequest;
use Gtlogistics\QuickbaseClient\Response\PaginatedResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

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
     * @return iterable<array<string, array{value: mixed}>>
     */
    public function queryRecords(QueryRequest $request): iterable
    {
        $httpRequest = $this->makeRequest('POST', '/v1/records/query');

        return $this->paginatedResponse($httpRequest, $request);
    }

    private function makeRequest(string $method, string $uri): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $this->baseUri . $uri);

        return $request
            ->withHeader('QB-Realm-Hostname', 'https://gtglobal.quickbase.com')
            ->withHeader('Authorization', "QB-USER-TOKEN $this->token");
    }

    /**
     * @return iterable<array<string, array{value: mixed}>>
     */
    private function paginatedResponse(RequestInterface $httpRequest, PaginableRequestInterface $request): iterable
    {
        while (true) {
            $httpRequest = $httpRequest->withBody($this->streamFactory->createStream(json_encode($request)));

            $httpResponse = $this->client->sendRequest($httpRequest);
            $response = new PaginatedResponse($httpResponse);

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
