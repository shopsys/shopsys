<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeZone;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class DisplayTimeZoneProvider implements DisplayTimeZoneProviderInterface
{
    public function __construct(
        protected readonly string $adminDisplayTimeZone,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function getDisplayTimeZoneByDomainId(int $domainId): DateTimeZone
    {
        return $this->domain->getDomainConfigById($domainId)->getDateTimeZone();
    }

    #[Override]
    public function getDisplayTimeZoneForAdmin(): DateTimeZone
    {
        return new DateTimeZone($this->adminDisplayTimeZone);
    }
}
