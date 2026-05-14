<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Guzzle;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Json;
use Throwable;

final class ApiCaller
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    public function urlReturnsOk(string $url): bool
    {
        try {
            $response = $this->client->send(new Request('GET', $url), ['http_errors' => false]);

            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return mixed[]
     */
    public function sendGetToJsonArray(string $url): array
    {
        $request = new Request('GET', $url);
        $response = $this->client->send($request);

        $json = $response->getBody()->getContents();

        return Json::decode($json, true);
    }

    /**
     * @param string[] $urls
     * @param array<string, string> $headers
     * @return string[]
     */
    public function sendGetsAsyncToStrings(array $urls, array $headers): array
    {
        $promises = [];

        foreach ($urls as $url) {
            $request = new Request('GET', $url, $headers);
            $promises[] = $this->client->sendAsync($request);
        }

        // Wait on all of the requests to complete. Throws a ConnectException if any of the requests fail
        /** @var \Psr\Http\Message\ResponseInterface[] $responses */
        $responses = Utils::unwrap($promises);

        $stringResponses = [];

        foreach ($responses as $response) {
            $stringResponses[] = $response->getBody()->getContents();
        }

        return $stringResponses;
    }
}
