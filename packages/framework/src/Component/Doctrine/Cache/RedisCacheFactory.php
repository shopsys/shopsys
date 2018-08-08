<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine\Cache;

use Doctrine\Common\Cache\RedisCache;
use Redis;

class RedisCacheFactory
{
    public function create(Redis $redis): \Doctrine\Common\Cache\RedisCache
    {
        $redisCache = new RedisCache();
        $redisCache->setRedis($redis);

        return $redisCache;
    }
}
