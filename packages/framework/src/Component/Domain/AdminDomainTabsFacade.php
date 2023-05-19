<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminDomainTabsFacade
{
    protected const SESSION_SELECTED_DOMAIN = 'selected_domain_id';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return int
     */
    public function getSelectedDomainId(): int
    {
        return $this->getSelectedDomainConfig()->getId();
    }

    /**
     * @param int $domainId
     */
    public function setSelectedDomainId(int $domainId): void
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $this->requestStack->getSession()->set(static::SESSION_SELECTED_DOMAIN, $domainConfig->getId());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getSelectedDomainConfig(): DomainConfig
    {
        try {
            $domainId = $this->requestStack->getSession()->get(static::SESSION_SELECTED_DOMAIN);

            return $this->domain->getDomainConfigById($domainId);
        } catch (InvalidDomainIdException | SessionNotFoundException) {
            $allDomains = $this->domain->getAll();

            return reset($allDomains);
        }
    }
}
