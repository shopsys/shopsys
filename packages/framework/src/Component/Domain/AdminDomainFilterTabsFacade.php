<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminDomainFilterTabsFacade
{
    protected const string SESSION_PREFIX = 'admin_domain_filter_tabs_';

    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param int[]|null $allowedDomainIds A selected domain outside these IDs is cleared. Null allows all domains available to the administrator.
     */
    public function getSelectedDomainId(string $namespace, ?array $allowedDomainIds = null): ?int
    {
        return $this->getSelectedDomainConfig($namespace, $allowedDomainIds)?->getId();
    }

    public function setSelectedDomainId(string $namespace, ?int $domainId): void
    {
        $this->requestStack->getSession()->set($this->getSessionKey($namespace), $domainId);
    }

    /**
     * @param int[]|null $allowedDomainIds A selected domain outside these IDs is cleared. Null allows all domains available to the administrator.
     */
    public function getSelectedDomainConfig(string $namespace, ?array $allowedDomainIds = null): ?DomainConfig
    {
        try {
            $domainId = $this->requestStack->getSession()->get($this->getSessionKey($namespace));

            if ($domainId === null) {
                return null;
            }

            if (!in_array($domainId, $this->domain->getAdminEnabledDomainIds(), true)
                || ($allowedDomainIds !== null && !in_array($domainId, $allowedDomainIds, true))
            ) {
                throw new InvalidDomainIdException();
            }

            return $this->domain->getDomainConfigById($domainId);
        } catch (InvalidDomainIdException|SessionNotFoundException) {
            $this->setSelectedDomainId($namespace, null);

            return null;
        }
    }

    protected function getSessionKey(string $namespace): string
    {
        return static::SESSION_PREFIX . $namespace;
    }
}
