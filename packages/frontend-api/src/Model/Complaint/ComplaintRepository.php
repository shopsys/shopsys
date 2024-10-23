<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @param string|null $search
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    public function getCustomerUserComplaintsLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
        ?string $search = null,
    ): array {
        $queryBuilder = $this->createCustomerUserComplaintsQueryBuilder($customerUser)
            ->orderBy('c.createdAt', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);


        return $this->applySearchToQueryBuilder($queryBuilder, $search)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param int $limit
     * @param int $offset
     * @param string|null $search
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    public function getCustomerComplaintsLimitedList(
        Customer $customer,
        int $limit,
        int $offset,
        ?string $search = null,
    ): array {
        $queryBuilder = $this->createCustomerComplaintsQueryBuilder($customer)
            ->orderBy('c.createdAt', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $this->applySearchToQueryBuilder($queryBuilder, $search)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string|null $search
     * @return int
     */
    public function getCustomerUserComplaintsListCount(
        CustomerUser $customerUser,
        ?string $search = null,
    ): int {
        $queryBuilder = $this->createCustomerUserComplaintsQueryBuilder($customerUser)
            ->select('COUNT(c)');

        return $this->applySearchToQueryBuilder($queryBuilder, $search)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param string|null $search
     * @return int
     */
    public function getCustomerComplaintsListCount(
        Customer $customer,
        ?string $search = null,
    ): int {
        $queryBuilder = $this->createCustomerComplaintsQueryBuilder($customer)
            ->select('COUNT(c)');

        return $this->applySearchToQueryBuilder($queryBuilder, $search)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createCustomerUserComplaintsQueryBuilder(
        CustomerUser $customerUser,
    ): QueryBuilder {
        return $this->createQueryBuilder()
            ->andWhere('c.customerUser = :customerUser')
            ->setParameter('customerUser', $customerUser);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createCustomerComplaintsQueryBuilder(
        Customer $customer,
    ): QueryBuilder {
        return $this->createQueryBuilder()
            ->andWhere('c.customer = :customer')
            ->setParameter('customer', $customer);
    }

    /**
     * @param string $complaintNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint|null
     */
    public function findByComplaintNumberAndCustomerUser(
        string $complaintNumber,
        CustomerUser $customerUser,
    ): ?Complaint {
        return $this->createQueryBuilder()
            ->andWhere('c.number = :complaintNumber')->setParameter('complaintNumber', $complaintNumber)
            ->andWhere('c.customerUser = :customerUser')->setParameter('customerUser', $customerUser)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $complaintNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint|null
     */
    public function findByComplaintNumberAndCustomer(
        string $complaintNumber,
        Customer $customer,
    ): ?Complaint {
        return $this->createQueryBuilder()
            ->andWhere('c.number = :complaintNumber')->setParameter('complaintNumber', $complaintNumber)
            ->andWhere('c.customer = :customerUser')->setParameter('customerUser', $customer)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->from(Complaint::class, 'c')
            ->select('c');
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string|null $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function applySearchToQueryBuilder(QueryBuilder $queryBuilder, ?string $search = null): QueryBuilder
    {
        if ($search === null) {
            return $queryBuilder;
        }

        return $queryBuilder
            ->leftJoin('c.items', 'ci')
            ->leftJoin('ci.orderItem', 'oi')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'NORMALIZED(c.number) LIKE NORMALIZED(:search)',
                    'NORMALIZED(oi.name) LIKE NORMALIZED(:search)',
                    'NORMALIZED(oi.catnum) LIKE NORMALIZED(:search)',
                ),
            )
            ->setParameter('search', DatabaseSearching::getFullTextLikeSearchString($search));
    }
}
