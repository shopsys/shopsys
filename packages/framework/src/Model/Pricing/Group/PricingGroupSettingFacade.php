<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class PricingGroupSettingFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly PricingGroupRepository $pricingGroupRepository,
        protected readonly Domain $domain,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly Setting $setting,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return bool
     */
    public function isPricingGroupUsedOnSelectedDomain(PricingGroup $pricingGroup): bool
    {
        return $this->pricingGroupRepository->existsCustomerUserWithPricingGroup($pricingGroup)
            || $this->isPricingGroupDefaultOnSelectedDomain($pricingGroup);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getDefaultPricingGroupByDomainId($domainId): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        $defaultPricingGroupId = $this->setting->getForDomain(Setting::DEFAULT_PRICING_GROUP, $domainId);

        return $this->pricingGroupRepository->getById($defaultPricingGroupId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getDefaultPricingGroupByCurrentDomain(): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        return $this->getDefaultPricingGroupByDomainId($this->domain->getId());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getDefaultPricingGroupBySelectedDomain(): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        return $this->getDefaultPricingGroupByDomainId($this->adminDomainTabsFacade->getSelectedDomainId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     */
    public function setDefaultPricingGroupForSelectedDomain(PricingGroup $pricingGroup): void
    {
        $this->setting->setForDomain(
            Setting::DEFAULT_PRICING_GROUP,
            $pricingGroup->getId(),
            $this->adminDomainTabsFacade->getSelectedDomainId(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return bool
     */
    public function isPricingGroupDefaultOnSelectedDomain(PricingGroup $pricingGroup): bool
    {
        return $pricingGroup === $this->getDefaultPricingGroupBySelectedDomain();
    }
}
