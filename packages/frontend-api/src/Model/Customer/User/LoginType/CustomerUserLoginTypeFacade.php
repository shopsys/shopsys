<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Doctrine\ORM\EntityManagerInterface;

class CustomerUserLoginTypeFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFactory $customerUserLoginTypeFactory
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeRepository $customerUserLoginTypeRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly CustomerUserLoginTypeFactory $customerUserLoginTypeFactory,
        protected readonly CustomerUserLoginTypeRepository $customerUserLoginTypeRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeData $customerUserLoginTypeData
     */
    public function updateCustomerUserLoginTypes(
        CustomerUserLoginTypeData $customerUserLoginTypeData,
    ): void {
        $customerUserLoginTypeExists = $this->customerUserLoginTypeRepository->existsByCustomerUserAndType(
            $customerUserLoginTypeData->customerUser,
            $customerUserLoginTypeData->loginType,
        );

        if ($customerUserLoginTypeExists === true) {
            return;
        }

        $customerUserLoginType = $this->customerUserLoginTypeFactory->create($customerUserLoginTypeData);
        $this->entityManager->persist($customerUserLoginType);

        $this->entityManager->flush();
    }
}
