<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Redis;

use Redis;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Tests\App\Test\FunctionalTestCase;

class RedisFacadeTest extends FunctionalTestCase
{
    private Redis $redisClient;

    protected function setUp(): void
    {
        $this->redisClient = self::getContainer()->get('snc_redis.test');
    }

    public function testCleanCache(): void
    {
        $this->redisClient->set('test', 'test');
        $facade = new RedisFacade([$this->redisClient], []);

        $facade->cleanCache();

        $this->assertFalse((bool)$this->redisClient->exists('test'));
    }

    public function testCleanCacheNoErrorOnEmpty(): void
    {
        $facade = new RedisFacade([$this->redisClient], []);

        $facade->cleanCache();

        $this->assertNull($this->redisClient->getLastError());
    }

    public function testNotCleaningPersistentClient(): void
    {
        $this->redisClient->set('test', 'test');
        $facade = new RedisFacade([$this->redisClient], [$this->redisClient]);

        $facade->cleanCache();

        $this->assertTrue((bool)$this->redisClient->exists('test'));
    }
}
