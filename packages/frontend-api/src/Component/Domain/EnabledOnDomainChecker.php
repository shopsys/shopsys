<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;

class EnabledOnDomainChecker
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param int[] $enabledDomainIds
     */
    public function __construct(protected readonly Domain $domain, protected readonly array $enabledDomainIds = [])
    {
    }

    /**
     * @return bool
     */
    public function isEnabledOnCurrentDomain(): bool
    {
        try {
            return in_array($this->domain->getId(), $this->enabledDomainIds, true);
        } catch (NoDomainSelectedException $e) {
            return false;
        }
    }
}
