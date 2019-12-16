<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class CustomerUserListAdminFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserRepository
     */
    protected $customerUserRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserRepository $customerUserRepository
     */
    public function __construct(CustomerUserRepository $customerUserRepository)
    {
        $this->customerUserRepository = $customerUserRepository;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerListQueryBuilderByQuickSearchData(
        $domainId,
        QuickSearchFormData $quickSearchData
    ) {
        return $this->customerUserRepository->getCustomerListQueryBuilderByQuickSearchData(
            $domainId,
            $quickSearchData
        );
    }
}
