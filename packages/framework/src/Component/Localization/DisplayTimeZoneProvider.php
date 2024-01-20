<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class DisplayTimeZoneProvider implements DisplayTimeZoneProviderInterface
{
    /**
     * @param string $adminDisplayTimeZone
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly string $adminDisplayTimeZone,
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

    /**
     * @return \DateTimeZone
     */
    public function getDisplayTimeZoneForAdmin(): DateTimeZone
    {
        return new DateTimeZone($this->adminDisplayTimeZone);
    }
}
