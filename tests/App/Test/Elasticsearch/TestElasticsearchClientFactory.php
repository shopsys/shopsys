<?php

declare(strict_types=1);

namespace Tests\App\Test\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchClientFactory;

class TestElasticsearchClientFactory extends ElasticsearchClientFactory
{
    /**
     * @param string $hosts
     * @return \Tests\App\Test\Elasticsearch\TestClientBuilder
     */
    public static function create(string $hosts): TestClientBuilder
    {
        $clientBuilder = new TestClientBuilder();

        $clientBuilder->setHosts(self::parseHosts($hosts));

        return $clientBuilder;
    }
}
