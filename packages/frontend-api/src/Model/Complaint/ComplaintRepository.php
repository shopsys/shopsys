<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class ComplaintRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    public function getCustomerUserComplaintsLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
        ComplaintFilter $filter,
    ): array {
        $queryBuilder = $this->createCustomerUserComplaintsQueryBuilder($customerUser)
            ->orderBy('c.createdAt', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->applyFilterToQueryBuilder($queryBuilder, $filter);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    public function getCustomerComplaintsLimitedList(
        Customer $customer,
        int $limit,
        int $offset,
        ComplaintFilter $filter,
    ): array {
        $queryBuilder = $this->createCustomerComplaintsQueryBuilder($customer)
            ->orderBy('c.createdAt', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->applyFilterToQueryBuilder($queryBuilder, $filter);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getCustomerUserComplaintsListCount(
        CustomerUser $customerUser,
        ComplaintFilter $filter,
    ): int {
        $queryBuilder = $this->createCustomerUserComplaintsQueryBuilder($customerUser)
            ->select('COUNT(DISTINCT c.id)');

        $this->applyFilterToQueryBuilder($queryBuilder, $filter);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function getCustomerComplaintsListCount(
        Customer $customer,
        ComplaintFilter $filter,
    ): int {
        $queryBuilder = $this->createCustomerComplaintsQueryBuilder($customer)
            ->select('COUNT(DISTINCT c.id)');

        $this->applyFilterToQueryBuilder($queryBuilder, $filter);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<int, int>
     */
    public function getCustomerUserComplaintStatusCounts(
        CustomerUser $customerUser,
        ComplaintFilter $filter,
    ): array {
        $queryBuilder = $this->createCustomerUserComplaintsQueryBuilder($customerUser)
            ->join('c.status', 'cs')
            ->select('cs.id AS statusId, COUNT(DISTINCT c.id) AS complaintsCount')
            ->groupBy('cs.id');

        $this->applyFilterToQueryBuilder($queryBuilder, $filter);

        return $this->extractComplaintStatusCountsByStatusId($queryBuilder->getQuery()->getArrayResult());
    }

    /**
     * @return array<int, int>
     */
    public function getCustomerComplaintStatusCounts(
        Customer $customer,
        ComplaintFilter $filter,
    ): array {
        $queryBuilder = $this->createCustomerComplaintsQueryBuilder($customer)
            ->join('c.status', 'cs')
            ->select('cs.id AS statusId, COUNT(DISTINCT c.id) AS complaintsCount')
            ->groupBy('cs.id');

        $this->applyFilterToQueryBuilder($queryBuilder, $filter);

        return $this->extractComplaintStatusCountsByStatusId($queryBuilder->getQuery()->getArrayResult());
    }

    protected function createCustomerUserComplaintsQueryBuilder(
        CustomerUser $customerUser,
    ): QueryBuilder {
        return $this->createQueryBuilder()
            ->andWhere('c.customerUser = :customerUser')
            ->setParameter('customerUser', $customerUser);
    }

    protected function createCustomerComplaintsQueryBuilder(
        Customer $customer,
    ): QueryBuilder {
        return $this->createQueryBuilder()
            ->andWhere('c.customer = :customer')
            ->setParameter('customer', $customer);
    }

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

    public function findByComplaintNumberAndCustomer(
        string $complaintNumber,
        Customer $customer,
    ): ?Complaint {
        return $this->createQueryBuilder()
            ->andWhere('c.number = :complaintNumber')->setParameter('complaintNumber', $complaintNumber)
            ->andWhere('c.customer = :customer')->setParameter('customer', $customer)
            ->getQuery()
            ->getOneOrNullResult();
    }

    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->from(Complaint::class, 'c')
            ->select('c');
    }

    protected function applyFilterToQueryBuilder(QueryBuilder $queryBuilder, ComplaintFilter $filter): void
    {
        if ($filter->getCreatedAfter() !== null) {
            $queryBuilder->andWhere('c.createdAt >= :createdAfter')
                ->setParameter('createdAfter', $filter->getCreatedAfter());
        }

        if ($filter->getCreatedBefore() !== null) {
            $queryBuilder->andWhere('c.createdAt <= :createdBefore')
                ->setParameter('createdBefore', $filter->getCreatedBefore());
        }

        if ($filter->getSearch() !== null) {
            $search = $this->databaseSearchingHelper->getFullTextLikeSearchString($filter->getSearch());

            $queryBuilder
                ->distinct()
                ->leftJoin('c.items', 'ci')
                ->leftJoin('ci.orderItem', 'oi')
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        'NORMALIZED(c.number) LIKE NORMALIZED(:search)',
                        'NORMALIZED(oi.name) LIKE NORMALIZED(:search)',
                        'NORMALIZED(oi.catnum) LIKE NORMALIZED(:search)',
                    ),
                )
                ->setParameter('search', $search);
        }

        if ($filter->getStatuses() === null || count($filter->getStatuses()) <= 0) {
            return;
        }

        $queryBuilder->andWhere('c.status IN (:statuses)')
            ->setParameter('statuses', $filter->getStatuses());
    }

    /**
     * @param array<int, array{statusId: int|string, complaintsCount: int|string}> $complaintStatusCounts
     * @return array<int, int>
     */
    protected function extractComplaintStatusCountsByStatusId(array $complaintStatusCounts): array
    {
        $complaintStatusCountsByStatusId = [];

        foreach ($complaintStatusCounts as $complaintStatusCount) {
            $complaintStatusCountsByStatusId[(int)$complaintStatusCount['statusId']] =
                (int)$complaintStatusCount['complaintsCount'];
        }

        return $complaintStatusCountsByStatusId;
    }
}
