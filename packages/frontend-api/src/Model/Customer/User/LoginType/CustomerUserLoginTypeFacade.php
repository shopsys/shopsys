<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CustomerUserLoginTypeFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly CustomerUserLoginTypeFactory $customerUserLoginTypeFactory,
        protected readonly CustomerUserLoginTypeRepository $customerUserLoginTypeRepository,
    ) {
    }

    public function updateCustomerUserLoginTypes(
        CustomerUserLoginTypeData $customerUserLoginTypeData,
    ): void {
        $existingCustomerUserLoginType = $this->customerUserLoginTypeRepository->findByCustomerUserAndType(
            $customerUserLoginTypeData->customerUser,
            $customerUserLoginTypeData->loginType,
        );

        if ($existingCustomerUserLoginType !== null) {
            $existingCustomerUserLoginType->setLastLoggedInAt($customerUserLoginTypeData->lastLoggedInAt);
            $this->entityManager->flush();

            return;
        }

        $newCustomerUserLoginType = $this->customerUserLoginTypeFactory->create($customerUserLoginTypeData);
        $this->entityManager->persist($newCustomerUserLoginType);

        $this->entityManager->flush();
    }

    public function findMostRecentLoginType(
        CustomerUser $customerUser,
        ?string $excludeType = null,
    ): ?CustomerUserLoginType {
        return $this->customerUserLoginTypeRepository->findMostRecentLoginType($customerUser, $excludeType);
    }

    /**
     * @return string[]
     */
    public function getAllLoginTypes(CustomerUser $customerUser, ?string $excludeType = null): array
    {
        return $this->customerUserLoginTypeRepository->getAllLoginTypes($customerUser, $excludeType);
    }
}
