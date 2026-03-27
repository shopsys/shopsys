<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DomainExtension extends AbstractExtension
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getDomain', $this->getDomain(...)),
            new TwigFunction('getDomainName', $this->getDomainNameById(...)),
            new TwigFunction('isMultidomain', $this->isMultidomain(...)),
            new TwigFunction('getFirstDomain', $this->getFirstDomainConfig(...)),
            new TwigFunction('getDomainsCount', $this->getDomainsCount(...)),
        ];
    }

    public function getDomain(): Domain
    {
        return $this->domain;
    }

    public function getName(): string
    {
        return 'domain';
    }

    public function getDomainNameById(int $domainId): string
    {
        return $this->getDomain()->getDomainConfigById($domainId)->getName();
    }

    public function isMultidomain(): bool
    {
        return $this->getDomain()->isMultidomain();
    }

    public function getFirstDomainConfig(): DomainConfig
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);
    }

    public function getDomainsCount(): int
    {
        return $this->inMemoryCache->getOrSaveValue(
            'domainsCount',
            function () {
                return count($this->domain->getAll());
            },
            'domainsCount
        ',
        );
    }
}
