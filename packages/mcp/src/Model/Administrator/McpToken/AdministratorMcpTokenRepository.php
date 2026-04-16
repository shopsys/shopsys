<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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

    public function findCurrentByAdministratorAndClient(
        Administrator $administrator,
        string $clientId,
    ): ?AdministratorMcpToken {
        return $this->getAdministratorMcpTokenEntityRepository()->createQueryBuilder('amt')
            ->where('amt.administrator = :administrator')
            ->andWhere('amt.clientId = :clientId')
            ->andWhere('amt.revokedAt IS NULL')
            ->andWhere('amt.replacedAt IS NULL')
            ->setParameter('administrator', $administrator)
            ->setParameter('clientId', $clientId)
            ->orderBy('amt.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCurrentByPublicTokenId(string $publicTokenId): ?AdministratorMcpToken
    {
        return $this->getAdministratorMcpTokenEntityRepository()->createQueryBuilder('amt')
            ->where('amt.publicTokenId = :publicTokenId')
            ->andWhere('amt.revokedAt IS NULL')
            ->andWhere('amt.replacedAt IS NULL')
            ->setParameter('publicTokenId', $publicTokenId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<\Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken>
     */
    public function findActiveConnectedClientTokensByAdministrator(
        Administrator $administrator,
        DateTimeImmutable $dateTime,
    ): array {
        return $this->getAdministratorMcpTokenEntityRepository()->createQueryBuilder('amt')
            ->where('amt.administrator = :administrator')
            ->andWhere('amt.clientId != :manualClientId')
            ->andWhere('amt.revokedAt IS NULL')
            ->andWhere('amt.replacedAt IS NULL')
            ->andWhere('amt.expiresAt > :dateTime')
            ->setParameter('administrator', $administrator)
            ->setParameter('manualClientId', AdministratorMcpToken::MANUAL_CLIENT_ID)
            ->setParameter('dateTime', $dateTime)
            ->orderBy('amt.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
