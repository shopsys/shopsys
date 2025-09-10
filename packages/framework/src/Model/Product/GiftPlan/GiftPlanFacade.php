<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;

class GiftPlanFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanRepository $giftPlanRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFactory $giftPlanFactory
     */
    public function __construct(
        protected readonly GiftPlanRepository $giftPlanRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly GiftPlanFactory $giftPlanFactory,
    ) {
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan
     */
    public function getById(int $id): GiftPlan
    {
        return $this->giftPlanRepository->getById($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanData $giftPlanData
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan
     */
    public function create(GiftPlanData $giftPlanData): GiftPlan
    {
        $giftPlan = $this->giftPlanFactory->create($giftPlanData);
        $this->em->persist($giftPlan);
        $this->em->flush();

        return $giftPlan;
    }

    /**
     * @param int $id
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanData $giftPlanData
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan
     */
    public function edit(int $id, GiftPlanData $giftPlanData): GiftPlan
    {
        $giftPlan = $this->getById($id);
        $giftPlan->edit($giftPlanData);
        $this->em->flush();

        return $giftPlan;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $giftPlan = $this->getById($id);
        $this->em->remove($giftPlan);
        $this->em->flush();
    }
}
