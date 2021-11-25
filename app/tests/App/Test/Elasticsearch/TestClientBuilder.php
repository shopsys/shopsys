<?php

declare(strict_types=1);

namespace Tests\App\Test\Elasticsearch;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Transport;

class TestClientBuilder extends ClientBuilder
{
    /**
     * @param \Elasticsearch\Transport $transport
     * @param callable $endpoint
     * @param array $registeredNamespaces
     * @return \Tests\App\Test\Elasticsearch\TestClient
     */
    protected function instantiate(Transport $transport, callable $endpoint, array $registeredNamespaces): TestClient
    {
        return new TestClient($transport, $endpoint, $registeredNamespaces);
    }
}
