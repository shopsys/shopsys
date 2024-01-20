<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeZone;

interface DisplayTimeZoneProviderInterface
{
    /**
     * @param int $domainId
     * @return \DateTimeZone
     */
    public function getDisplayTimeZoneByDomainId(int $domainId): DateTimeZone;

    /**
     * @return \DateTimeZone
     */
    public function getDisplayTimeZoneForAdmin(): DateTimeZone;
}
