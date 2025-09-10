<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\Exception\GiftPlanNotFoundException;

class GiftPlanRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(GiftPlan::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan|null
     */
    public function findById(int $id): ?GiftPlan
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan
     */
    public function getById(int $id): GiftPlan
    {
        $giftPlan = $this->findById($id);

        if ($giftPlan === null) {
            throw new GiftPlanNotFoundException('Gift plan with ID ' . $id . ' does not exist.');
        }

        return $giftPlan;
    }
}
