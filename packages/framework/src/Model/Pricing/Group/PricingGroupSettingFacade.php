<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class PricingGroupSettingFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository
     */
    protected $pricingGroupRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    public function __construct(
        PricingGroupRepository $pricingGroupRepository,
        Domain $domain,
        AdminDomainTabsFacade $adminDomainTabsFacade,
        Setting $setting
    ) {
        $this->pricingGroupRepository = $pricingGroupRepository;
        $this->domain = $domain;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->setting = $setting;
    }

    public function isPricingGroupUsedOnSelectedDomain(PricingGroup $pricingGroup): bool
    {
        return $this->pricingGroupRepository->existsUserWithPricingGroup($pricingGroup)
            || $this->isPricingGroupDefaultOnSelectedDomain($pricingGroup);
    }

    /**
     * @param int $domainId
     */
    public function getDefaultPricingGroupByDomainId($domainId): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        $defaultPricingGroupId = $this->setting->getForDomain(Setting::DEFAULT_PRICING_GROUP, $domainId);

        return $this->pricingGroupRepository->getById($defaultPricingGroupId);
    }

    public function getDefaultPricingGroupByCurrentDomain(): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        return $this->getDefaultPricingGroupByDomainId($this->domain->getId());
    }

    public function getDefaultPricingGroupBySelectedDomain(): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        return $this->getDefaultPricingGroupByDomainId($this->adminDomainTabsFacade->getSelectedDomainId());
    }

    public function setDefaultPricingGroupForSelectedDomain(PricingGroup $pricingGroup)
    {
        $this->setting->setForDomain(Setting::DEFAULT_PRICING_GROUP, $pricingGroup->getId(), $this->adminDomainTabsFacade->getSelectedDomainId());
    }

    public function isPricingGroupDefaultOnSelectedDomain(PricingGroup $pricingGroup): bool
    {
        return $pricingGroup === $this->getDefaultPricingGroupBySelectedDomain();
    }
}
