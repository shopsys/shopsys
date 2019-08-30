<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeZone;

interface DisplayTimeZoneProviderInterface
{
    /**
     * @return \DateTimeZone
     */
    public function getDisplayTimeZone(): DateTimeZone;
}
