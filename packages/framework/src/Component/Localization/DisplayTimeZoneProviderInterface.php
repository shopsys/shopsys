<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeZone;

interface DisplayTimeZoneProviderInterface
{
    public function getDisplayTimeZoneByDomainId(int $domainId): DateTimeZone;

    public function getDisplayTimeZoneForAdmin(): DateTimeZone;
}
