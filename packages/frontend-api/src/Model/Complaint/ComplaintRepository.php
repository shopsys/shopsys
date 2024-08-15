<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use App\Model\Customer\User\CustomerUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;

class ComplaintRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    public function getCustomerUserComplaintsLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
    ): array {
        return $this->createCustomerUserComplaintsQueryBuilder($customerUser)
            ->orderBy('c.createdAt', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return int
     */
    public function getCustomerUserComplaintsListCount(CustomerUser $customerUser): int
    {
        return $this->createCustomerUserComplaintsQueryBuilder($customerUser)
            ->select('COUNT(c)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createCustomerUserComplaintsQueryBuilder(
        CustomerUser $customerUser,
    ): QueryBuilder {
        return $this->em->createQueryBuilder()
            ->from(Complaint::class, 'c')
            ->select('c')
            ->andWhere('c.customerUser = :customerUser')->setParameter('customerUser', $customerUser);
    }
}
