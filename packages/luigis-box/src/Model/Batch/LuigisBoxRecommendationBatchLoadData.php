<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

class LuigisBoxRecommendationBatchLoadData extends LuigisBoxBatchLoadData
{
    /**
     * @param string $type
     * @param string $endpoint
     * @param string $userIdentifier
     * @param int $limit
     * @param string $recommenderClientIdentifier
     * @param string[] $itemIds
     */
    public function __construct(
        string $type,
        string $endpoint,
        string $userIdentifier,
        int $limit,
        protected readonly string $recommenderClientIdentifier,
        protected readonly array $itemIds = [],
    ) {
        parent::__construct($type, $endpoint, $userIdentifier, $limit);
    }

    /**
     * @return string[]
     */
    public function getItemIds(): array
    {
        return $this->itemIds;
    }

    /**
     * @return string
     */
    public function getRecommenderClientIdentifier(): string
    {
        return $this->recommenderClientIdentifier;
    }
}
