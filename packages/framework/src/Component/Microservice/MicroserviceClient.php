<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Microservice;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class MicroserviceClient
{
    public function __construct(protected readonly Client $guzzleClient)
    {
    }

    /**
     * @return mixed
     */
    public function get(string $resource, array $parameters = [])
    {
        $options = array_merge(
            $this->createDefaultOptions(),
            [RequestOptions::QUERY => $parameters],
        );

        $response = $this->guzzleClient->get($resource, $options);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @return mixed
     */
    public function post(string $resource, array $parameters = [])
    {
        $options = $this->createJsonOptions($parameters);
        $response = $this->guzzleClient->post($resource, $options);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @return mixed
     */
    public function delete(string $resource, array $parameters = [])
    {
        $options = $this->createJsonOptions($parameters);
        $response = $this->guzzleClient->delete($resource, $options);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @return mixed
     */
    public function patch(string $resource, array $parameters = [])
    {
        $options = $this->createJsonOptions($parameters);
        $response = $this->guzzleClient->patch($resource, $options);

        return json_decode($response->getBody()->getContents());
    }

    protected function createDefaultOptions(): array
    {
        return [
            RequestOptions::CONNECT_TIMEOUT => 15,
            RequestOptions::TIMEOUT => 15,
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];
    }

    /**
     * @param array<string, mixed> $jsonData
     * @return array<string, mixed>
     */
    protected function createJsonOptions(array $jsonData): array
    {
        return array_merge(
            $this->createDefaultOptions(),
            [RequestOptions::JSON => $jsonData],
        );
    }
}
