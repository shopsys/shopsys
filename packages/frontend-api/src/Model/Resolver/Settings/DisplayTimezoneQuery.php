<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class DisplayTimezoneQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    public function displayTimezoneQuery(): string
    {
        return $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($this->domain->getId())->getName();
    }
}
