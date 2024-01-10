<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class DisplayTimeZoneProvider implements DisplayTimeZoneProviderInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param int $domainId
     * @return \DateTimeZone
     */
    public function getDisplayTimeZoneByDomainId(int $domainId): DateTimeZone
    {
        return $this->domain->getDomainConfigById($domainId)->getDateTimeZone();
    }
}
