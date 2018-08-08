<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User;

class PricingGroupRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getPricingGroupRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(PricingGroup::class);
    }

    public function getById(int $pricingGroupId): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        $pricingGroup = $this->getPricingGroupRepository()->find($pricingGroupId);
        if ($pricingGroup === null) {
            $message = 'Pricing group with ID ' . $pricingGroupId . ' not found.';
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Group\Exception\PricingGroupNotFoundException($message);
        }
        return $pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getAll(): array
    {
        return $this->getPricingGroupRepository()->findAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getPricingGroupsByDomainId(int $domainId): array
    {
        return $this->getPricingGroupRepository()->findBy(['domainId' => $domainId]);
    }

    public function findById(int $pricingGroupId): ?\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        return $this->getPricingGroupRepository()->find($pricingGroupId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getAllExceptIdByDomainId(int $pricingGroupId, int $domainId): array
    {
        $qb = $this->getPricingGroupRepository()->createQueryBuilder('pg')
            ->where('pg.domainId = :domainId')
            ->andWhere('pg.id != :id')
            ->setParameters(['domainId' => $domainId, 'id' => $pricingGroupId]);

        return $qb->getQuery()->getResult();
    }

    public function existsUserWithPricingGroup(PricingGroup $pricingGroup): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(u)
            FROM ' . User::class . ' u
            WHERE u.pricingGroup = :pricingGroup')
            ->setParameter('pricingGroup', $pricingGroup);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }
}
