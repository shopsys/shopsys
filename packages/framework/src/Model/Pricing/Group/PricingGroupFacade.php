<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPriceRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PricingGroupFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPriceRepository $productCalculatedPriceRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFactoryInterface $pricingGroupFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PricingGroupRepository $pricingGroupRepository,
        protected readonly Domain $domain,
        protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ProductCalculatedPriceRepository $productCalculatedPriceRepository,
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly PricingGroupFactoryInterface $pricingGroupFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param int $pricingGroupId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getById($pricingGroupId)
    {
        return $this->pricingGroupRepository->getById($pricingGroupId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function create(PricingGroupData $pricingGroupData, $domainId)
    {
        $pricingGroup = $this->pricingGroupFactory->create($pricingGroupData, $domainId);

        $this->em->persist($pricingGroup);
        $this->em->flush();

        $this->productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();
        $this->productVisibilityFacade->createAndRefreshProductVisibilitiesForPricingGroup(
            $pricingGroup,
            $pricingGroup->getDomainId(),
        );
        $this->productCalculatedPriceRepository->createProductCalculatedPricesForPricingGroup($pricingGroup);

        $this->dispatchPricingGroupEvent($pricingGroup, PricingGroupEvent::CREATE);

        return $pricingGroup;
    }

    /**
     * @param int $pricingGroupId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function edit($pricingGroupId, PricingGroupData $pricingGroupData)
    {
        $pricingGroup = $this->pricingGroupRepository->getById($pricingGroupId);
        $pricingGroup->edit($pricingGroupData);

        $this->em->flush();

        $this->productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();

        $this->dispatchPricingGroupEvent($pricingGroup, PricingGroupEvent::UPDATE);

        return $pricingGroup;
    }

    /**
     * @param int $oldPricingGroupId
     * @param int|null $newPricingGroupId
     */
    public function delete($oldPricingGroupId, $newPricingGroupId = null)
    {
        $oldPricingGroup = $this->pricingGroupRepository->getById($oldPricingGroupId);

        if ($newPricingGroupId !== null) {
            $newPricingGroup = $this->pricingGroupRepository->getById($newPricingGroupId);
            $this->customerUserRepository->replaceCustomerUsersPricingGroup($oldPricingGroup, $newPricingGroup);
        } else {
            $newPricingGroup = null;
        }

        if (
            $newPricingGroup !== null
            && $this->pricingGroupSettingFacade->isPricingGroupDefaultOnSelectedDomain($oldPricingGroup)
        ) {
            $this->pricingGroupSettingFacade->setDefaultPricingGroupForSelectedDomain($newPricingGroup);
        }

        $this->em->remove($oldPricingGroup);

        $this->dispatchPricingGroupEvent($oldPricingGroup, PricingGroupEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getAll()
    {
        return $this->pricingGroupRepository->getAll();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getByDomainId($domainId)
    {
        return $this->pricingGroupRepository->getPricingGroupsByDomainId($domainId);
    }

    /**
     * @param int $id
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getAllExceptIdByDomainId($id, $domainId)
    {
        return $this->pricingGroupRepository->getAllExceptIdByDomainId($id, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[][]
     */
    public function getAllIndexedByDomainId()
    {
        $pricingGroupsByDomainId = [];

        foreach ($this->domain->getAll() as $domain) {
            $domainId = $domain->getId();
            $pricingGroupsByDomainId[$domainId] = $this->pricingGroupRepository->getPricingGroupsByDomainId($domainId);
        }

        return $pricingGroupsByDomainId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $eventType
     * @see \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupEvent class
     */
    protected function dispatchPricingGroupEvent(PricingGroup $pricingGroup, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new PricingGroupEvent($pricingGroup), $eventType);
    }
}
