<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Elasticsearch\ClientBuilder;
use JsonException;

class ElasticsearchClientFactory
{
    /**
     * @param string $hosts
     * @return \Elasticsearch\ClientBuilder
     */
    public static function create(string $hosts): ClientBuilder
    {
        $clientBuilder = new ClientBuilder();

        $clientBuilder->setHosts(self::parseHosts($hosts));

        return $clientBuilder;
    }

    /**
     * @param string $hosts
     * @return string[]
     */
    protected static function parseHosts(string $hosts): array
    {
        try {
            return json_decode($hosts, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return [$hosts];
        }
    }
}
