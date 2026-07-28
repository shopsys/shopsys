<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

class ProductReviewEnabledChecker
{
    /**
     * @param int[] $enabledDomainIds
     */
    public function __construct(protected readonly array $enabledDomainIds)
    {
    }

    public function isEnabledForDomain(int $domainId): bool
    {
        return in_array($domainId, $this->enabledDomainIds, true);
    }
}
