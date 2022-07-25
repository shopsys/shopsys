<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @param mixed[] $options
     * @param mixed[] $server
     * @return \Tests\App\Test\Client
     */
    protected static function createClient(array $options = [], array $server = []): Client
    {
        /** @var \Tests\App\Test\Client $client */
        $client = parent::createClient($options, $server);

        return $client;
    }
}
