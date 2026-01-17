<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;

abstract class RedisDomainQueueFacade
{
    public function __construct(
        protected readonly Redis $redisQueue,
    ) {
    }

    public function getCount(int $domainId): int
    {
        return $this->redisQueue->sCard($this->createDomainRedisKey($domainId));
    }

    /**
     * @param mixed $value
     */
    protected function add($value, int $domainId): void
    {
        $this->redisQueue->sAdd($this->createDomainRedisKey($domainId), $value);
    }

    /**
     * @param mixed[] $values
     */
    protected function addBatch(array $values, int $domainId): void
    {
        $this->redisQueue->sAddArray($this->createDomainRedisKey($domainId), $values);
    }

    /**
     * @return mixed[]
     */
    protected function getValues(int $domainId, int $batchSize): array
    {
        $data = $this->redisQueue->sRandMember($this->createDomainRedisKey($domainId), $batchSize);

        if ($data) {
            $this->redisQueue->sRem($this->createDomainRedisKey($domainId), ...$data);
        }

        return $data;
    }

    protected function createDomainRedisKey(int $domainId): string
    {
        return 'domain-' . $domainId;
    }
}
