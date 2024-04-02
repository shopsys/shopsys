<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;

class TransferIssueRepository
{
    public const int TRANSFER_ISSUES_KEEP_DAYS_LIMIT = 7;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(TransferIssue::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ti')
            ->from(TransferIssue::class, 'ti');
    }

    /**
     * @param \DateTime $fromDateTime
     * @return int
     */
    public function getTransferIssuesCountFrom(DateTime $fromDateTime): int
    {
        return $this->getQueryBuilder()
            ->select('COUNT(ti) as count')
            ->where('ti.deletedAt IS NULL')
            ->andWhere('ti.createdAt > :fromDateTime')
            ->setParameter('fromDateTime', $fromDateTime)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \DateTime $fromDateTime
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTransferIssuesQueryBuilderForDataGrid(DateTime $fromDateTime): QueryBuilder
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

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssue|null
     */
    public function findById(int $id): ?TransferIssue
    {
        return $this->getRepository()->find($id);
    }

    public function deleteOldTransferIssues(): void
    {
        $removeIssuesOfOlderDate = new DateTimeImmutable('- ' . self::TRANSFER_ISSUES_KEEP_DAYS_LIMIT . ' days midnight');
        $this->em->getConnection()->executeStatement(
            'DELETE FROM transfer_issues WHERE created_at < :removeIssuesOfOlderDate',
            ['removeIssuesOfOlderDate' => $removeIssuesOfOlderDate],
            ['removeIssuesOfOlderDate' => Types::DATETIME_IMMUTABLE],
        );
    }
}
