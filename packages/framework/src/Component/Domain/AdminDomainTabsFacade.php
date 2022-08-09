<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AdminDomainTabsFacade
{
    protected const SESSION_SELECTED_DOMAIN = 'selected_domain_id';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(Domain $domain, SessionInterface $session)
    {
        $this->domain = $domain;
        $this->session = $session;
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
        $this->session->set(static::SESSION_SELECTED_DOMAIN, $domainConfig->getId());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getSelectedDomainConfig(): DomainConfig
    {
        try {
            $domainId = $this->session->get(static::SESSION_SELECTED_DOMAIN);
            return $this->domain->getDomainConfigById($domainId);
        } catch (InvalidDomainIdException $e) {
            $allDomains = $this->domain->getAll();
            return reset($allDomains);
        }
    }
}
