<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AdminDomainTabsFacade
{
    const SESSION_SELECTED_DOMAIN = 'selected_domain_id';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    public function __construct(Domain $domain, SessionInterface $session)
    {
        $this->domain = $domain;
        $this->session = $session;
    }

    public function getSelectedDomainId()
    {
        return $this->getSelectedDomainConfig()->getId();
    }

    /**
     * @param int $domainId
     */
    public function setSelectedDomainId($domainId)
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $this->session->set(self::SESSION_SELECTED_DOMAIN, $domainConfig->getId());
    }

    public function getSelectedDomainConfig()
    {
        try {
            $domainId = $this->session->get(self::SESSION_SELECTED_DOMAIN);
            return $this->domain->getDomainConfigById($domainId);
        } catch (\Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException $e) {
            $allDomains = $this->domain->getAll();
            return reset($allDomains);
        }
    }
}
