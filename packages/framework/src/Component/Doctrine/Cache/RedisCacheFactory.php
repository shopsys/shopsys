<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine\Cache;

use Doctrine\Common\Cache\RedisCache;
use Redis;

/**
 * @deprecated This factory class is deprecated since SSFW 8.1, use setter injection of the Redis instance in DIC configuration of the RedisCache service instead
 * @see https://symfony.com/doc/3.4/service_container/calls.html
 */
class RedisCacheFactory
{
    /**
     * @param \Redis $redis
     * @return \Doctrine\Common\Cache\RedisCache
     */
    public function create(Redis $redis)
    {
        @trigger_error(sprintf('The factory class %s is deprecated, use setter injection of the Redis instance in DIC configuration of the RedisCache service instead', __CLASS__), E_USER_DEPRECATED);

        $redisCache = new RedisCache();
        $redisCache->setRedis($redis);

        return $redisCache;
    }
}
