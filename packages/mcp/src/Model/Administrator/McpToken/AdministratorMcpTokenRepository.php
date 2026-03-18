<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

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

    public function findActiveByAdministrator(Administrator $administrator): ?AdministratorMcpToken
    {
        return $this->getAdministratorMcpTokenEntityRepository()->createQueryBuilder('amt')
            ->where('amt.administrator = :administrator')
            ->andWhere('amt.revokedAt IS NULL')
            ->andWhere('amt.replacedAt IS NULL')
            ->setParameter('administrator', $administrator)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveByPublicTokenId(string $publicTokenId): ?AdministratorMcpToken
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
}
