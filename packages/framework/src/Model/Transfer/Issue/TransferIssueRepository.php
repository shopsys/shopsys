<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;

class TransferIssueRepository
{
    public const int TRANSFER_ISSUES_KEEP_DAYS_LIMIT = 7;

    public function __construct(
        protected EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(TransferIssue::class);
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ti')
            ->from(TransferIssue::class, 'ti');
    }

    public function getTransferIssuesCountFrom(DateTimeImmutable $fromDateTime): int
    {
        return $this->getQueryBuilder()
            ->select('COUNT(ti) as count')
            ->where('ti.deletedAt IS NULL')
            ->andWhere('ti.createdAt > :fromDateTime')
            ->setParameter('fromDateTime', $fromDateTime)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTransferIssuesQueryBuilderForDataGrid(DateTimeImmutable $fromDateTime): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->select('ti, t')
            ->join(Transfer::class, 't', Join::WITH, 'ti.transfer = t')
            ->where('ti.deletedAt IS NULL')
            ->andWhere('ti.createdAt > :fromDateTime')
            ->setParameter('fromDateTime', $fromDateTime)
            ->orderBy('ti.createdAt', 'DESC')
            ->addOrderBy('ti.id', 'DESC');
    }

    public function findById(int $id): ?TransferIssue
    {
        return $this->getRepository()->find($id);
    }

    public function deleteOldTransferIssues(): void
    {
        $removeIssuesOfOlderDate = $this->clock->now()->modify('- ' . self::TRANSFER_ISSUES_KEEP_DAYS_LIMIT . ' days midnight');
        $this->em->getConnection()->executeStatement(
            'DELETE FROM transfer_issues WHERE created_at < :removeIssuesOfOlderDate',
            ['removeIssuesOfOlderDate' => $removeIssuesOfOlderDate],
            ['removeIssuesOfOlderDate' => Types::DATETIME_IMMUTABLE],
        );
    }
}
