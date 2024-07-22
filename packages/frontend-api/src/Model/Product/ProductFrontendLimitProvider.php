<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product;

class ProductFrontendLimitProvider
{
    /**
     * @param int $bestsellingProductsFrontendLimit
     * @param int $productAccessoriesFrontendLimit
     * @param int $promotedProductsFrontendLimit
     * @param int $recommendedProductsFrontendLimit
     * @param int $relatedProductsFrontendLimit
     */
    public function __construct(
        protected int $bestsellingProductsFrontendLimit = 30,
        protected int $productAccessoriesFrontendLimit = 30,
        protected int $promotedProductsFrontendLimit = 30,
        protected int $recommendedProductsFrontendLimit = 30,
        protected int $relatedProductsFrontendLimit = 30,
    ) {
    }

    /**
     * @return int
     */
    public function getBestsellingProductsFrontendLimit(): int
    {
        return $this->bestsellingProductsFrontendLimit;
    }

    /**
     * @return int
     */
    public function getProductAccessoriesFrontendLimit(): int
    {
        return $this->productAccessoriesFrontendLimit;
    }

    /**
     * @return int
     */
    public function getPromotedProductsFrontendLimit(): int
    {
        return $this->promotedProductsFrontendLimit;
    }

    /**
     * @return int
     */
    public function getRecommendedProductsFrontendLimit(): int
    {
        return $this->recommendedProductsFrontendLimit;
    }

    /**
     * @return int
     */
    public function getRelatedProductsFrontendLimit(): int
    {
        return $this->relatedProductsFrontendLimit;
    }
}
