<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GiftPlanFacade
{
    public function __construct(
        protected readonly GiftPlanRepository $giftPlanRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly GiftPlanFactory $giftPlanFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getById(int $id): GiftPlan
    {
        return $this->giftPlanRepository->getById($id);
    }

    public function create(GiftPlanData $giftPlanData): GiftPlan
    {
        $giftPlan = $this->giftPlanFactory->create($giftPlanData);
        $this->em->persist($giftPlan);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new GiftPlanEvent($giftPlan->getMainProducts()), GiftPlanEvent::CREATE);

        return $giftPlan;
    }

    public function edit(int $id, GiftPlanData $giftPlanData): GiftPlan
    {
        $giftPlan = $this->getById($id);
        $originalMainProducts = $giftPlan->getMainProducts();
        $giftPlan->edit($giftPlanData);
        $this->em->flush();

        $this->eventDispatcher->dispatch(
            new GiftPlanEvent(array_merge($originalMainProducts, $giftPlan->getMainProducts())),
            GiftPlanEvent::UPDATE,
        );

        return $giftPlan;
    }

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
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Product[]>
     */
    public function findActiveGiftProductsByMainProducts(array $mainProducts, int $domainId): array
    {
        return $this->giftPlanRepository->findActiveGiftProductsByMainProducts($mainProducts, $domainId);
    }

    /**
     * @param int[] $mainProductIds
     * @return int[][]
     */
    public function findActiveGiftProductIdsByMainProductIds(array $mainProductIds, int $domainId): array
    {
        return $this->giftPlanRepository->findActiveGiftProductIdsByMainProductIds($mainProductIds, $domainId);
    }
}
