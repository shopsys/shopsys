<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class CustomerUserListAdminFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository
     */
    protected $customerUserRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
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
    public function getCustomerUserListQueryBuilderByQuickSearchData(
        int $domainId,
        QuickSearchFormData $quickSearchData
    ): QueryBuilder {
        return $this->customerUserRepository->getCustomerUserListQueryBuilderByQuickSearchData(
            $domainId,
            $quickSearchData
        );
    }
}
