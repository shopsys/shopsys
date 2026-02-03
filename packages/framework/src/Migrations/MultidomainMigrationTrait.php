<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Migrations\Exception\DomainNotSetException;

trait MultidomainMigrationTrait
{
    protected ?Domain $domain = null;

    protected function getDomain(): Domain
    {
        if ($this->domain !== null) {
            return $this->domain;
        }

        throw new DomainNotSetException(static::class);
    }

    /**
     * @return int[]
     */
    protected function getAllDomainIds(): array
    {
        $domainIds = [];

        foreach ($this->getDomain()->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $domainIds[] = $domainConfig->getId();
        }

        return $domainIds;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    protected function getAllDomainConfigs(): array
    {
        return $this->getDomain()->getAllIncludingDomainConfigsWithoutDataCreated();
    }

    protected function getDomainLocale(int $domainId): string
    {
        return $this->getDomain()->getDomainConfigById($domainId)->getLocale();
    }

    /**
     * @return string[]
     */
    protected function getAllLocales(): array
    {
        $domainLocales = [];

        foreach ($this->getDomain()->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $domainLocales[] = $domainConfig->getLocale();
        }

        return array_unique($domainLocales);
    }

    public function setDomain(Domain $domain): void
    {
        $this->domain = $domain;
    }
}
