<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class IndependentTransportVisibilityCalculation
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isIndependentlyVisible(Transport $transport, $domainId)
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        $transportName = $transport->getName($locale);

        if ($transportName === '' || $transportName === null) {
            return false;
        }

        if ($transport->isHidden() || $transport->isDeleted()) {
            return false;
        }

        return $transport->isEnabled($domainId);
    }
}
