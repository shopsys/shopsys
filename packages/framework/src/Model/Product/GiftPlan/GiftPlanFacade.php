<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GiftPlanFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanRepository $giftPlanRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFactory $giftPlanFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected readonly GiftPlanRepository $giftPlanRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly GiftPlanFactory $giftPlanFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
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

        $this->eventDispatcher->dispatch(new GiftPlanEvent($giftPlan->getMainProducts()), GiftPlanEvent::CREATE);

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

        $this->eventDispatcher->dispatch(new GiftPlanEvent($giftPlan->getMainProducts()), GiftPlanEvent::UPDATE);

        return $giftPlan;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $giftPlan = $this->getById($id);
        $mainProductIds = $giftPlan->getMainProducts();
        $this->em->remove($giftPlan);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new GiftPlanEvent($mainProductIds), GiftPlanEvent::DELETE);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $mainProducts
     * @param int $domainId
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Product[]>
     */
    public function findActiveGiftProductsByMainProducts(array $mainProducts, int $domainId): array
    {
        return $this->giftPlanRepository->findActiveGiftProductsByMainProducts($mainProducts, $domainId);
    }

    /**
     * @param int[] $mainProductIds
     * @param int $domainId
     * @return int[][]
     */
    public function findActiveGiftProductIdsByMainProductIds(array $mainProductIds, int $domainId): array
    {
        return $this->giftPlanRepository->findActiveGiftProductIdsByMainProductIds($mainProductIds, $domainId);
    }
}
