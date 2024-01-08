<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class DisplayTimezoneQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     */
    public function __construct(
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    /**
     * @return string
     */
    public function displayTimezoneQuery(): string
    {
        return $this->displayTimeZoneProvider->getDisplayTimeZone()->getName();
    }
}
