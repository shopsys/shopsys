<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewPolicyFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductReviewMinimalAverageRatingForListingQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductReviewPolicyFacade $productReviewPolicyFacade,
    ) {
    }

    public function productReviewMinimalAverageRatingForListingQuery(): ?float
    {
        return $this->productReviewPolicyFacade->findMinimalAverageRatingForListingByDomainId($this->domain->getId());
    }
}
