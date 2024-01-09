<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class DisplayTimezoneQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return string
     */
    public function displayTimezoneQuery(): string
    {
        return $this->domain->getDateTimeZone()->getName();
    }
}
