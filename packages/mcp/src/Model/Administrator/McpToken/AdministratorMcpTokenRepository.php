<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorMcpTokenRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken>
     */
    protected function getAdministratorMcpTokenEntityRepository(): EntityRepository
    {
        return $this->em->getRepository(AdministratorMcpToken::class);
    }

    public function findActiveByIdAndAdministrator(
        Administrator $administrator,
        int $id,
        DateTimeImmutable $dateTime,
    ): ?AdministratorMcpToken {
        return $this->createActiveTokenQueryBuilder($dateTime)
            ->andWhere('amt.id = :id')
            ->andWhere('amt.administrator = :administrator')
            ->setParameter('id', $id)
            ->setParameter('administrator', $administrator)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveByPublicTokenId(
        string $publicTokenId,
        DateTimeImmutable $dateTime,
    ): ?AdministratorMcpToken {
        return $this->createActiveTokenQueryBuilder($dateTime)
            ->andWhere('amt.publicTokenId = :publicTokenId')
            ->setParameter('publicTokenId', $publicTokenId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<\Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken>
     */
    public function findActiveTokensByAdministrator(
        Administrator $administrator,
        DateTimeImmutable $dateTime,
    ): array {
        return $this->createActiveTokenQueryBuilder($dateTime)
            ->andWhere('amt.administrator = :administrator')
            ->setParameter('administrator', $administrator)
            ->orderBy('amt.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    protected function createActiveTokenQueryBuilder(DateTimeImmutable $dateTime): QueryBuilder
    {
        return $this->getAdministratorMcpTokenEntityRepository()->createQueryBuilder('amt')
            ->where('amt.revokedAt IS NULL')
            ->andWhere('amt.expiresAt > :dateTime')
            ->setParameter('dateTime', $dateTime);
    }
}
