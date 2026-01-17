<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class CustomerUserListAdminFacade
{
    public function __construct(protected readonly CustomerUserRepository $customerUserRepository)
    {
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerUserListQueryBuilderByQuickSearchData(
        $domainId,
        QuickSearchFormData $quickSearchData,
    ) {
        return $this->customerUserRepository->getCustomerUserListQueryBuilderByQuickSearchData(
            $domainId,
            $quickSearchData,
        );
    }
}
