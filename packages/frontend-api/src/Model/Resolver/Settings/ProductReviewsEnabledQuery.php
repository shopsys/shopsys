<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductReviewsEnabledQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
    ) {
    }

    public function productReviewsEnabledQuery(): bool
    {
        return $this->productReviewEnabledChecker->isEnabledForDomain($this->domain->getId());
    }
}
