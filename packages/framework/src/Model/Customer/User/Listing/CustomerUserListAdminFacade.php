<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Listing;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class CustomerUserListAdminFacade
{
    public function __construct(protected readonly CustomerUserListAdminRepository $customerUserListAdminRepository)
    {
    }

    public function getCustomerUserListQueryBuilderByQuickSearchData(
        int $domainId,
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        return $this->customerUserListAdminRepository->getCustomerUserListQueryBuilderByQuickSearchData(
            $domainId,
            $quickSearchData,
        );
    }

    public function getCustomerUserListQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->customerUserListAdminRepository->getCustomerUserListQueryBuilder($domainId);
    }
}
