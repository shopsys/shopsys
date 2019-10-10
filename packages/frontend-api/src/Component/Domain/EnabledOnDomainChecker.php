<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;

class EnabledOnDomainChecker
{
    /**
     * @var int[]
     */
    protected $enabledDomainIds;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param int[] $enabledDomainIds
     */
    public function __construct(Domain $domain, array $enabledDomainIds = [])
    {
        $this->enabledDomainIds = $enabledDomainIds;
        $this->domain = $domain;
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
