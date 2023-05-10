<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\Comparison;

use App\Model\Product\Comparison\Comparison;
use App\Model\Product\Comparison\ComparisonFacade;
use App\Model\Product\Comparison\Exception\ComparisonNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ComparisonQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Product\Comparison\ComparisonFacade $comparisonFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        private readonly ComparisonFacade $comparisonFacade,
        private readonly CurrentCustomerUser $currentCustomerUser
    ) {
    }

    /**
     * @param string|null $uuid
     * @return \App\Model\Product\Comparison\Comparison|null
     */
    public function comparisonByUuidQuery(?string $uuid): ?Comparison
    {
        $loggedCustomerComparison = null;
        $comparisonByUuid = null;
        /** @var \App\Model\Customer\User\CustomerUser $currentCustomerUser */
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser !== null) {
            $loggedCustomerComparison = $this->comparisonFacade->findComparisonOfCustomerUser($currentCustomerUser);
        }

        if (
            $uuid !== null
            && ($loggedCustomerComparison === null
                || $loggedCustomerComparison->getUuid()
                !== $uuid)
        ) {
            try {
                $comparisonByUuid = $this->comparisonFacade->getComparisonByUuid($uuid);
            } catch (ComparisonNotFoundException $exception) {
            }
        }

        if ($loggedCustomerComparison !== null && $comparisonByUuid !== null) {
            return $this->comparisonFacade->mergeComparisons($loggedCustomerComparison, $comparisonByUuid);
        }

        if ($currentCustomerUser !== null && $comparisonByUuid !== null) {
            return $this->comparisonFacade->setCustomerUserToComparison($currentCustomerUser, $comparisonByUuid);
        }

        return $loggedCustomerComparison ?? $comparisonByUuid;
    }
}
