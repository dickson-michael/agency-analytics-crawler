<?php

namespace MyCrawler;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Wrapper interface around PSR client construction. Can fetch resources on your behalf and provide
 * useful metadata about the query!
 *
 * The existence of this is pretty much to allow for additional metadata to be extracted about the
 * query in specialized contexts. With future over-engineering, this probably will be transformed
 * into a middleware / plugin processor where various components can register to provide more data.
 */
class CrawlerAgent
{
    /** @var ClientInterface A PSR-7 client configured to execute requests. */
    private $client;

    /** @var RequestFactoryInterface A request factory that can generate PSR-7 requests. */
    private $requestFactory;

    /**
     * @param ClientInterface $client PSR-7 client configured to execute requests.
     * @param RequestFactoryInterface $requestFactory Request factory to generate PSR-7 requests.
     */
    public function __construct(ClientInterface $client, RequestFactoryInterface $requestFactory)
    {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Fetches a given web address using the HTTP GET method.
     *
     * @param string $url The URL to GET the HTML contents from.
     *
     * @throws NetworkExceptionInterface If there is an error communicating with the server.
     *
     * @return mixed[] Metadata (and HTML contents) from the URL.
     */
    public function fetch(string $url) : array
    {
        $request = $this->requestFactory->createRequest('GET', $url);

        $timeStart = microtime(true); // keep immediately preceeding sendRequest()
        $response = $this->client->sendRequest($request);
        $body = $response->getBody();
        $html = $body->getContents();
        $timeTaken = microtime(true) - $timeStart; // keep immediately following getContents()

        // TODO: Check for valid HTML or recode this as 'data' if we want to let the scraper detect content type.

        return [
            'url' => $url,
            'code' => $response->getStatusCode(),
            'html' => $html,
            'time' => $timeTaken,
        ];
    }
}
