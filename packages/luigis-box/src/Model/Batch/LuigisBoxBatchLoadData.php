<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

abstract class LuigisBoxBatchLoadData
{
    public function __construct(
        protected readonly string $type,
        protected readonly string $endpoint,
        protected readonly string $userIdentifier,
        protected readonly int $limit,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
