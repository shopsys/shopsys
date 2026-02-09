<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class CustomerUserListAdminFacade
{
    public function __construct(protected readonly CustomerUserRepository $customerUserRepository)
    {
    }

    public function getCustomerUserListQueryBuilderByQuickSearchData(
        int $domainId,
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        return $this->customerUserRepository->getCustomerUserListQueryBuilderByQuickSearchData(
            $domainId,
            $quickSearchData,
        );
    }
}
