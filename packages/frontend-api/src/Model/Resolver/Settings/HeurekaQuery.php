<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class HeurekaQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly HeurekaFacade $heurekaFacade,
    ) {
    }

    /**
     * @return bool
     */
    public function heurekaEnabledQuery(): bool
    {
        return $this->heurekaFacade->isDomainLocaleSupported($this->domain->getLocale()) &&
            $this->heurekaFacade->isHeurekaShopCertificationActivated($this->domain->getId());
    }
}
