<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
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

    public function isPricingGroupUsedOnDomain(PricingGroup $pricingGroup, DomainConfig $domainConfig): bool
    {
        return $this->pricingGroupRepository->existsCustomerUserWithPricingGroup($pricingGroup)
            || $this->isPricingGroupDefaultOnDomain($pricingGroup, $domainConfig);
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

    public function getDefaultPricingGroupByDomain(DomainConfig $domainConfig): PricingGroup
    {
        return $this->getDefaultPricingGroupByDomainId($domainConfig->getId());
    }

    public function setDefaultPricingGroupForDomain(PricingGroup $pricingGroup, DomainConfig $domainConfig): void
    {
        $this->setting->setForDomain(
            Setting::DEFAULT_PRICING_GROUP,
            $pricingGroup->getId(),
            $domainConfig->getId(),
        );
    }

    public function isPricingGroupDefaultOnDomain(PricingGroup $pricingGroup, DomainConfig $domainConfig): bool
    {
        return $pricingGroup === $this->getDefaultPricingGroupByDomain($domainConfig);
    }
}
