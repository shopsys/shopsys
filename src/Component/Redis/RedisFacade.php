<?php

declare(strict_types=1);

namespace App\Component\Redis;

use Shopsys\FrameworkBundle\Component\Redis\RedisFacade as BaseRedisFacade;

class RedisFacade extends BaseRedisFacade
{
    public function closeAllClients(): void
    {
        foreach ($this->allClients as $redis) {
            $redis->close();
        }
    }
}
