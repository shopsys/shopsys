<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\AdditionalService;

class AdditionalServicesBatchLoadData
{
    /**
     * @param int[] $additionalServiceIds
     */
    public function __construct(
        protected readonly int $productId,
        protected readonly array $additionalServiceIds,
    ) {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return int[]
     */
    public function getAdditionalServiceIds(): array
    {
        return $this->additionalServiceIds;
    }
}
