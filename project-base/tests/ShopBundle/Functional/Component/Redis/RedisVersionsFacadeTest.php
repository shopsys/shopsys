<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Component\Redis;

use Shopsys\FrameworkBundle\Component\Redis\RedisVersionsFacade;
use Tests\ShopBundle\Test\FunctionalTestCase;

class RedisVersionsFacadeTest extends FunctionalTestCase
{
    public function testCleanOldCache(): void
    {
        $currentVersionKey = '20190308130159.test';
        $oldVersionKey = '20190308130158.test';
        $notVersionKey = '2019030813015.test';

        $redisClient = $this->getContainer()->get('snc_redis.test');
        $redisClient->set($currentVersionKey, 'test');
        $redisClient->set($oldVersionKey, 'test');
        $redisClient->set($notVersionKey, 'test');
        $facade = new RedisVersionsFacade($redisClient, '20190308130159');

        $facade->cleanOldCache();
        $this->assertSame(0, $redisClient->exists($oldVersionKey));
        $this->assertSame(1, $redisClient->exists($currentVersionKey));
        $this->assertSame(1, $redisClient->exists($notVersionKey));

        // cleanup
        $redisClient->del($currentVersionKey);
        $redisClient->del($notVersionKey);
    }
}
