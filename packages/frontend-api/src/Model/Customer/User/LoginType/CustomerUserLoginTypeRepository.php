<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginType|null
     */
    public function findByCustomerUserAndType(
        CustomerUser $customerUser,
        string $loginType,
    ): ?CustomerUserLoginType {
        return $this->entityManager->getRepository(CustomerUserLoginType::class)
            ->findOneBy([
                'customerUser' => $customerUser,
                'loginType' => $loginType,
            ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string|null $excludeType
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginType|null
     */
    public function findMostRecentLoginType(
        CustomerUser $customerUser,
        ?string $excludeType = null,
    ): ?CustomerUserLoginType {
        return $this->getOrderedCustomerUserLoginTypeQueryBuilder($customerUser, $excludeType)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string|null $excludeType
     * @return string[]
     */
    public function getAllLoginTypes(CustomerUser $customerUser, ?string $excludeType = null): array
    {
        $result = $this->getOrderedCustomerUserLoginTypeQueryBuilder($customerUser, $excludeType)
            ->select('cult.loginType')
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'loginType');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string|null $excludeType
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getOrderedCustomerUserLoginTypeQueryBuilder(
        CustomerUser $customerUser,
        ?string $excludeType = null,
    ): QueryBuilder {
        $queryBuilder = $this->entityManager->getRepository(CustomerUserLoginType::class)
            ->createQueryBuilder('cult')
            ->where('cult.customerUser = :customerUser')
            ->setParameter('customerUser', $customerUser)
            ->orderBy('cult.lastLoggedInAt', 'DESC');

        if ($excludeType !== null) {
            $queryBuilder
                ->andWhere('cult.loginType != :excludeType')
                ->setParameter('excludeType', $excludeType);
        }

        return $queryBuilder;
    }
}
