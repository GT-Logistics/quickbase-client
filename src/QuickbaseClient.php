<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient;

use Gtlogistics\QuickbaseClient\Authentication\UserTokenAuthentication;
use Gtlogistics\QuickbaseClient\Exceptions\MultipleRecordsFoundException;
use Gtlogistics\QuickbaseClient\Exceptions\QuickbaseException;
use Gtlogistics\QuickbaseClient\Requests\DeleteRecordsRequest;
use Gtlogistics\QuickbaseClient\Requests\FindRecordRequest;
use Gtlogistics\QuickbaseClient\Requests\PaginableRequestInterface;
use Gtlogistics\QuickbaseClient\Requests\QueryRecordsRequest;
use Gtlogistics\QuickbaseClient\Requests\RunReportRequest;
use Gtlogistics\QuickbaseClient\Requests\UpsertRecordsRequest;
use Gtlogistics\QuickbaseClient\Responses\DeletedRecordsResponse;
use Gtlogistics\QuickbaseClient\Responses\PaginatedRecordsResponse;
use Gtlogistics\QuickbaseClient\Responses\RecordsResponse;
use Gtlogistics\QuickbaseClient\Responses\ResponseInterface;
use Gtlogistics\QuickbaseClient\Utils\RequestUtils;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HeaderSetPlugin;
use Http\Client\Common\PluginClient;
use JsonSerializable;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface as HttpRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Webmozart\Assert\Assert;

final class QuickbaseClient
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        string $token,
        string $realm,
        string $baseUri = 'https://api.quickbase.com'
    ) {
        $this->client = new PluginClient(
            $client,
            [
                new BaseUriPlugin($uriFactory->createUri($baseUri)),
                new HeaderSetPlugin(['QB-Realm-Hostname' => $realm]),
                new AuthenticationPlugin(new UserTokenAuthentication($token)),
            ],
        );
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @api
     *
     * @return iterable<array<positive-int, mixed>
     */
    public function upsertRecords(UpsertRecordsRequest $request): iterable
    {
        $httpRequest = $this->makeRequest('POST', '/v1/records', $request);

        return $this->recordsResponse($httpRequest);
    }

    /**
     * @api
     *
     * @return iterable<array<positive-int, mixed>>
     */
    public function queryRecords(QueryRecordsRequest $request): iterable
    {
        $httpRequest = $this->makeRequest('POST', '/v1/records/query', $request);

        return $this->paginatedRecordsResponse($httpRequest, $request);
    }

    /**
     * @api
     *
     * @return array<positive-int, mixed>|null
     * @throws MultipleRecordsFoundException
     */
    public function findRecord(FindRecordRequest $request): ?iterable
    {
        $httpRequest = $this->makeRequest('POST', '/v1/records/query', $request);

        return $this->recordResponse($httpRequest);
    }

    /**
     * @api
     */
    public function deleteRecords(DeleteRecordsRequest $request): int
    {
        $httpRequest = $this->makeRequest('DELETE', '/v1/records', $request);

        return $this->doRequest($httpRequest, DeletedRecordsResponse::class)->getNumberDeleted();
    }

    public function runReport(RunReportRequest $request): iterable
    {
        $httpRequest = $this->makeRequest('POST', "/v1/reports/{$request->getReportId()}/run?" . http_build_query([
            'tableId' => $request->getTableId(),
            'top' => $request->getTop(),
            'skip' => $request->getSkip(),
        ]));

        return $this->paginatedRecordsResponse($httpRequest, $request);
    }

    private function makeRequest(string $method, string $uri, JsonSerializable $payload = null): HttpRequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        return RequestUtils::withPayload($request, $this->streamFactory, $payload);
    }

    /**
     * @template T of ResponseInterface
     * @param class-string<T> $responseClass
     *
     * @return T
     */
    private function doRequest(HttpRequestInterface $httpRequest, string $responseClass): ResponseInterface
    {
        $httpResponse = $this->client->sendRequest($httpRequest);
        if ($httpResponse->getStatusCode() >= 400 && $httpResponse->getStatusCode() <= 599) {
            throw QuickbaseException::fromResponse($httpResponse);
        }

        return $responseClass::fromResponse($httpResponse);
    }

    /**
     * @return iterable<array<positive-int, array{value: mixed}>>
     */
    private function recordsResponse(HttpRequestInterface $httpRequest): iterable
    {
        return $this->doRequest($httpRequest, RecordsResponse::class)->getData();
    }

    /**
     * @return array<positive-int, array{value: mixed}>
     *
     * @throws MultipleRecordsFoundException
     */
    private function recordResponse(HttpRequestInterface $httpRequest): ?array
    {
        $response = $this->doRequest($httpRequest, PaginatedRecordsResponse::class);

        if ($response->getTotalRecords() > 1) {
            throw new MultipleRecordsFoundException('The query return more that one record');
        }

        foreach ($response->getData() as $record) {
            return $record;
        }

        return null;
    }

    /**
     * @return iterable<array<positive-int, array{value: mixed}>>
     */
    private function paginatedRecordsResponse(HttpRequestInterface $httpRequest, PaginableRequestInterface $request): iterable
    {
        while (true) {
            $response = $this->doRequest($httpRequest, PaginatedRecordsResponse::class);

            foreach ($response->getData() as $record) {
                yield $record;
            }

            if (!$response->hasNext()) {
                break;
            }

            $paginationType = $request->getPaginationType();
            if ($paginationType === PaginableRequestInterface::PAGINATION_IN_PAYLOAD) {
                Assert::isInstanceOf($request, JsonSerializable::class);

                $httpRequest = RequestUtils::withPayload($httpRequest, $this->streamFactory, $request->withSkip($response->getNext()));
            } elseif ($paginationType === PaginableRequestInterface::PAGINATION_IN_QUERY) {
                $httpRequest = RequestUtils::withQuery($httpRequest, ['skip' => $response->getNext()]);
            } else {
                throw new \RuntimeException(sprintf('Unexpected pagination type %s', $paginationType));
            }
        }
    }
}
