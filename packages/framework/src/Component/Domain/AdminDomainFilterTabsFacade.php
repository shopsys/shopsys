<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminDomainFilterTabsFacade
{
    protected const SESSION_PREFIX = 'admin_domain_filter_tabs_';

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string $namespace
     * @return int|null
     */
    public function getSelectedDomainId(string $namespace): ?int
    {
        return $this->getSelectedDomainConfig($namespace)?->getId();
    }

    /**
     * @param string $namespace
     * @param int|null $domainId
     */
    public function setSelectedDomainId(string $namespace, ?int $domainId): void
    {
        $this->requestStack->getSession()->set($this->getSessionKey($namespace), $domainId);
    }

    /**
     * @param string $namespace
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig|null
     */
    public function getSelectedDomainConfig(string $namespace): ?DomainConfig
    {
        try {
            $domainId = $this->requestStack->getSession()->get($this->getSessionKey($namespace));

            if ($domainId === null) {
                return null;
            }

            return $this->domain->getDomainConfigById($domainId);
        } catch (InvalidDomainIdException|SessionNotFoundException) {
            $this->requestStack->getSession()->set($this->getSessionKey($namespace), null);

            return null;
        }
    }

    /**
     * @param string $namespace
     * @return string
     */
    protected function getSessionKey(string $namespace): string
    {
        return static::SESSION_PREFIX . $namespace;
    }
}
