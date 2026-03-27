<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminDomainTabsFacade
{
    protected const string SESSION_SELECTED_DOMAIN = 'selected_domain_id';

    public const string QUERY_PARAMETER_NAME = 'switchAdminDomainTo';

    public function __construct(
        protected readonly Domain $domain,
        protected readonly RequestStack $requestStack,
    ) {
    }

    public function getSelectedDomainId(): int
    {
        return $this->getSelectedDomainConfig()->getId();
    }

    public function setSelectedDomainId(int $domainId): void
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $this->requestStack->getSession()->set(static::SESSION_SELECTED_DOMAIN, $domainConfig->getId());
    }

    public function getSelectedDomainConfig(): DomainConfig
    {
        try {
            $domainId = $this->requestStack->getSession()->get(static::SESSION_SELECTED_DOMAIN);

            if (!in_array($domainId, $this->domain->getAdminEnabledDomainIds(), true)) {
                throw new InvalidDomainIdException();
            }

            return $this->domain->getDomainConfigById($domainId);
        } catch (InvalidDomainIdException|SessionNotFoundException) {
            $allowedDomainIds = $this->domain->getAdminEnabledDomainIds();
            $firstAllowedDomainId = array_first($allowedDomainIds);

            $this->setSelectedDomainId($firstAllowedDomainId);

            return $this->domain->getDomainConfigById($firstAllowedDomainId);
        }
    }
}
