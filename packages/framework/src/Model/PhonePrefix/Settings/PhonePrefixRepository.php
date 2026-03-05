<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix\Settings;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PhonePrefixRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefix>
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(PhonePrefix::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefix[]
     */
    public function findAllByDomainId(int $domainId): array
    {
        return $this->getRepository()->findBy(['domainId' => $domainId]);
    }

    public function deleteAllByDomainId(int $domainId): void
    {
        $this->em->createQueryBuilder()
            ->delete(PhonePrefix::class, 'pp')
            ->where('pp.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int[] $domainIds
     * @return int[]
     */
    public function filterOutConfiguredDomainIds(array $domainIds): array
    {
        $configuredDomains = $this->getRepository()
            ->createQueryBuilder('pp')
            ->indexBy('pp', 'pp.domainId')
            ->where('pp.domainId IN (:domainIds)')
            ->setParameter('domainIds', $domainIds)
            ->getQuery()
            ->getResult();

        return array_values(array_diff($domainIds, array_keys($configuredDomains)));
    }
}
