<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Console;

use Shopsys\FrameworkBundle\Component\Console\Exception\NoDomainSetException;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Console\Style\SymfonyStyle;

class DomainChoiceHandler
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function chooseDomainConfig(SymfonyStyle $io): DomainConfig
    {
        $domainConfigs = $this->domain->getAll();

        if (count($domainConfigs) === 0) {
            throw new NoDomainSetException();
        }

        $firstDomainConfig = reset($domainConfigs);

        if (count($domainConfigs) === 1) {
            return $firstDomainConfig;
        }

        $domainChoices = [];

        foreach ($domainConfigs as $domainConfig) {
            $domainChoices[$domainConfig->getId()] = $domainConfig->getName();
        }
        $chosenDomainName = $io->choice(
            'There is more than one domain. Which domain do you want to use?',
            $domainChoices,
            $firstDomainConfig->getName(),
        );

        foreach ($domainConfigs as $domainConfig) {
            if ($domainConfig->getName() === $chosenDomainName) {
                return $domainConfig;
            }
        }

        throw new NoDomainSetException();
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     */
    public function chooseDomainAndSwitch(SymfonyStyle $io): void
    {
        $domainConfig = $this->chooseDomainConfig($io);

        $this->domain->switchDomainById($domainConfig->getId());
    }
}
