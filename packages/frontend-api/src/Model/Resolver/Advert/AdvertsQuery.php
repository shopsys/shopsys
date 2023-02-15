<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class AdvertsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade $advertFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly Domain $domain
    ) {
    }

    /**
     * @param string|null $positionName
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function advertsQuery(?string $positionName = null): array
    {
        $domainId = $this->domain->getId();
        if ($positionName === null) {
            return $this->advertFacade->getVisibleAdvertsByDomainId($domainId);
        }
        return $this->advertFacade->getVisibleAdvertsByDomainIdAndPositionName($domainId, $positionName);
    }
}
