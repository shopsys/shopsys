<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

abstract class LuigisBoxBatchLoadData
{
    /**
     * @param string $type
     * @param string $endpoint
     * @param string $userIdentifier
     * @param int|null $limit
     */
    public function __construct(
        protected readonly string $type,
        protected readonly string $endpoint,
        protected readonly string $userIdentifier,
        protected readonly ?int $limit,
    ) {
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
