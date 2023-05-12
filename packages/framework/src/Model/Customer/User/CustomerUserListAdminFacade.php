<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class CustomerUserListAdminFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     */
    public function __construct(protected readonly CustomerUserRepository $customerUserRepository)
    {
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerUserListQueryBuilderByQuickSearchData(
        $domainId,
        QuickSearchFormData $quickSearchData
    ) {
        return $this->customerUserRepository->getCustomerUserListQueryBuilderByQuickSearchData(
            $domainId,
            $quickSearchData
        );
    }
}
