<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Migrations\Exception\ContainerNotSetException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This trait can be used in classes
 * that extend \Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration.
 */
trait MultidomainMigrationTrait
{
    protected ContainerInterface $container;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected function getDomain(): Domain
    {
        if (!isset($this->container)) {
            throw new ContainerNotSetException(static::class);
        }

        return $this->container->get(Domain::class);
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
     * @param int $domainId
     * @return string
     */
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

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface|null $container
     */
    public function setContainer(?ContainerInterface $container = null): void
    {
        $this->container = $container;
    }
}
