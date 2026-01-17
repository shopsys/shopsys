<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class HeurekaQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly HeurekaFacade $heurekaFacade,
    ) {
    }

    public function heurekaEnabledQuery(): bool
    {
        return $this->heurekaFacade->isDomainLocaleSupported($this->domain->getLocale()) &&
            $this->heurekaFacade->isHeurekaShopCertificationActivated($this->domain->getId());
    }
}
