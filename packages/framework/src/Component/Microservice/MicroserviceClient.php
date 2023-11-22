<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Microservice;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class MicroserviceClient
{
    /**
     * @param \GuzzleHttp\Client $guzzleClient
     */
    public function __construct(protected readonly Client $guzzleClient)
    {
    }

    /**
     * @param string $resource
     * @param mixed[] $parameters
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
     * @param string $resource
     * @param mixed[] $parameters
     * @return mixed
     */
    public function post(string $resource, array $parameters = [])
    {
        $options = $this->createJsonOptions($parameters);
        $response = $this->guzzleClient->post($resource, $options);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param string $resource
     * @param mixed[] $parameters
     * @return mixed
     */
    public function delete(string $resource, array $parameters = [])
    {
        $options = $this->createJsonOptions($parameters);
        $response = $this->guzzleClient->delete($resource, $options);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param string $resource
     * @param mixed[] $parameters
     * @return mixed
     */
    public function patch(string $resource, array $parameters = [])
    {
        $options = $this->createJsonOptions($parameters);
        $response = $this->guzzleClient->patch($resource, $options);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @return int[]|array<'Accept', 'application/json'>[]
     */
    protected function createDefaultOptions(): array
    {
        return [
            RequestOptions::CONNECT_TIMEOUT => 15,
            RequestOptions::TIMEOUT => 15,
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];
    }

    /**
     * @param mixed[] $jsonData
     * @return non-empty-array
     */
    protected function createJsonOptions(array $jsonData)
    {
        return array_merge(
            $this->createDefaultOptions(),
            [RequestOptions::JSON => $jsonData],
        );
    }
}
