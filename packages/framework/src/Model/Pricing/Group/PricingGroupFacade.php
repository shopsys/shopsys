<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\Exception\InvalidPricingGroupReplacementException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\Exception\PricingGroupIsUsedException;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PricingGroupFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PricingGroupRepository $pricingGroupRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly PricingGroupFactory $pricingGroupFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly Domain $domain,
    ) {
    }

    public function getById(int $pricingGroupId): PricingGroup
    {
        return $this->pricingGroupRepository->getById($pricingGroupId);
    }

    public function create(
        PricingGroupData $pricingGroupData,
        int $domainId,
    ): PricingGroup {
        $pricingGroup = $this->pricingGroupFactory->create($pricingGroupData, $domainId);

        $this->em->persist($pricingGroup);
        $this->em->flush();

        $this->productVisibilityFacade->createAndRefreshProductVisibilitiesForPricingGroup(
            $pricingGroup,
            $pricingGroup->getDomainId(),
        );

        $this->dispatchPricingGroupEvent($pricingGroup, PricingGroupEvent::CREATE);

        return $pricingGroup;
    }

    public function edit(
        int $pricingGroupId,
        PricingGroupData $pricingGroupData,
    ): PricingGroup {
        $pricingGroup = $this->pricingGroupRepository->getById($pricingGroupId);
        $pricingGroup->edit($pricingGroupData);

        $this->em->flush();

        $this->dispatchPricingGroupEvent($pricingGroup, PricingGroupEvent::UPDATE);

        return $pricingGroup;
    }

    public function delete(
        int $oldPricingGroupId,
        ?int $newPricingGroupId = null,
    ): void {
        $oldPricingGroup = $this->pricingGroupRepository->getById($oldPricingGroupId);
        $domainConfig = $this->domain->getDomainConfigById($oldPricingGroup->getDomainId());

        if ($newPricingGroupId === null) {
            if ($this->pricingGroupSettingFacade->isPricingGroupUsedOnDomain($oldPricingGroup, $domainConfig)) {
                throw new PricingGroupIsUsedException(
                    sprintf('Pricing group with ID %d is used and cannot be deleted without a replacement.', $oldPricingGroupId),
                );
            }
        } else {
            $newPricingGroup = $this->pricingGroupRepository->getById($newPricingGroupId);

            if ($newPricingGroup === $oldPricingGroup || $newPricingGroup->getDomainId() !== $oldPricingGroup->getDomainId()) {
                throw new InvalidPricingGroupReplacementException(
                    sprintf('Pricing group with ID %d cannot replace pricing group with ID %d.', $newPricingGroupId, $oldPricingGroupId),
                );
            }

            $this->customerUserRepository->replaceCustomerUsersPricingGroup($oldPricingGroup, $newPricingGroup);

            if ($this->pricingGroupSettingFacade->isPricingGroupDefaultOnDomain($oldPricingGroup, $domainConfig)) {
                $this->pricingGroupSettingFacade->setDefaultPricingGroupForDomain($newPricingGroup, $domainConfig);
            }
        }

        $this->em->remove($oldPricingGroup);

        $this->dispatchPricingGroupEvent($oldPricingGroup, PricingGroupEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getAll(): array
    {
        return $this->pricingGroupRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getByDomainId(int $domainId): array
    {
        return $this->pricingGroupRepository->getPricingGroupsByDomainId($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getAllExceptIdByDomainId(int $id, int $domainId): array
    {
        return $this->pricingGroupRepository->getAllExceptIdByDomainId($id, $domainId);
    }

    /**
     * @see \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupEvent class
     */
    protected function dispatchPricingGroupEvent(PricingGroup $pricingGroup, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new PricingGroupEvent($pricingGroup), $eventType);
    }
}
