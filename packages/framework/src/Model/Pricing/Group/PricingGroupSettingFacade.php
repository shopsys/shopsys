<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class PricingGroupSettingFacade
{
    public function __construct(
        protected readonly PricingGroupRepository $pricingGroupRepository,
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly PricingSetting $pricingSetting,
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

    /**
     * Returns the default pricing group of the first domain whose default currency is the given currency
     */
    public function findDefaultPricingGroupByCurrency(Currency $currency): ?PricingGroup
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            if ($this->pricingSetting->getDomainDefaultCurrencyIdByDomainId($domainId) === $currency->getId()) {
                return $this->getDefaultPricingGroupByDomainId($domainId);
            }
        }

        return null;
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
