<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class McpChangeRequestRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequest>
     */
    protected function getMcpChangeRequestEntityRepository(): EntityRepository
    {
        return $this->em->getRepository(McpChangeRequest::class);
    }

    public function findOpenByIdAndAdministrator(int $id, Administrator $administrator): ?McpChangeRequest
    {
        return $this->createOpenByAdministratorQueryBuilder($administrator)
            ->andWhere('mcr.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getOpenByAdministratorPaginationResult(
        Administrator $administrator,
        int $page,
        int $pageSize,
    ): PaginationResult {
        $queryBuilder = $this->createOpenByAdministratorQueryBuilder($administrator)
            ->orderBy('mcr.createdAt', 'DESC')
            ->addOrderBy('mcr.id', 'DESC');

        return new QueryPaginator($queryBuilder)->getResult($page, $pageSize);
    }

    public function countOpenByAdministrator(Administrator $administrator): int
    {
        return (int)$this->createOpenByAdministratorQueryBuilder($administrator)
            ->select('COUNT(mcr.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findPendingByIdForUpdate(int $id): ?McpChangeRequest
    {
        return $this->getMcpChangeRequestEntityRepository()->createQueryBuilder('mcr')
            ->where('mcr.id = :id')
            ->andWhere('mcr.status = :status')
            ->setParameter('id', $id)
            ->setParameter('status', McpChangeRequestStatusEnum::PENDING)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();
    }

    public function saveFailure(McpChangeRequest $mcpChangeRequest): void
    {
        $this->em->createQueryBuilder()
            ->update(McpChangeRequest::class, 'mcr')
            ->set('mcr.status', ':status')
            ->set('mcr.errorMessage', ':errorMessage')
            ->where('mcr.id = :id')
            ->setParameter('status', $mcpChangeRequest->getStatus())
            ->setParameter('errorMessage', $mcpChangeRequest->getErrorMessage())
            ->setParameter('id', $mcpChangeRequest->getId())
            ->getQuery()
            ->execute();
    }

    public function delete(McpChangeRequest $mcpChangeRequest): void
    {
        $this->em->createQueryBuilder()
            ->delete(McpChangeRequest::class, 'mcr')
            ->where('mcr.id = :id')
            ->setParameter('id', $mcpChangeRequest->getId())
            ->getQuery()
            ->execute();
    }

    protected function createOpenByAdministratorQueryBuilder(Administrator $administrator): QueryBuilder
    {
        return $this->getMcpChangeRequestEntityRepository()->createQueryBuilder('mcr')
            ->where('mcr.administrator = :administrator')
            ->setParameter('administrator', $administrator);
    }
}
