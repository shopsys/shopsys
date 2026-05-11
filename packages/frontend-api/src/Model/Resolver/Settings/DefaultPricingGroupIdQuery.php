<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class DefaultPricingGroupIdQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    public function defaultPricingGroupIdQuery(): int
    {
        return $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($this->domain->getId())->getId();
    }
}
