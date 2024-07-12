<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CustomerUserLoginTypeRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $loginType
     * @return bool
     */
    public function existsByCustomerUserAndType(
        CustomerUser $customerUser,
        string $loginType,
    ): bool {
        return $this->entityManager->getRepository(CustomerUserLoginType::class)
            ->count([
                'customerUser' => $customerUser,
                'loginType' => $loginType,
            ]) > 0;
    }
}
