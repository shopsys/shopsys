<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class PricingGroupSettingFacade
{
    public function __construct(
        protected readonly PricingGroupRepository $pricingGroupRepository,
        protected readonly Domain $domain,
        protected readonly Setting $setting,
    ) {
    }

    public function isPricingGroupUsed(PricingGroup $pricingGroup): bool
    {
        return $this->pricingGroupRepository->existsCustomerUserWithPricingGroup($pricingGroup)
            || $this->isPricingGroupSetAsDefault($pricingGroup);
    }

    public function getDefaultPricingGroupByDomainId(int $domainId): PricingGroup
    {
        $defaultPricingGroupId = $this->setting->getForDomain(Setting::DEFAULT_PRICING_GROUP, $domainId);

        return $this->pricingGroupRepository->getById($defaultPricingGroupId);
    }

    /**
     * @return array<int, int>
     */
    public function getAllDefaultPricingGroupsIdsIndexedByDomainId(): array
    {
        $defaultPricingGroupIdsByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $defaultPricingGroupIdsByDomainId[$domainId] = $this->setting->getForDomain(Setting::DEFAULT_PRICING_GROUP, $domainId);
        }

        return $defaultPricingGroupIdsByDomainId;
    }

    public function getDefaultPricingGroupByCurrentDomain(): PricingGroup
    {
        return $this->getDefaultPricingGroupByDomainId($this->domain->getId());
    }

    public function setPricingGroupAsDefault(PricingGroup $pricingGroup): void
    {
        $this->setting->setForDomain(
            Setting::DEFAULT_PRICING_GROUP,
            $pricingGroup->getId(),
            $pricingGroup->getDomainId(),
        );
    }

    public function isPricingGroupSetAsDefault(PricingGroup $pricingGroup): bool
    {
        return $pricingGroup === $this->getDefaultPricingGroupByDomainId($pricingGroup->getDomainId());
    }
}
