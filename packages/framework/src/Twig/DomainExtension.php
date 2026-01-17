<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
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
    public function getFunctions()
    {
        return [
            new TwigFunction('getDomain', $this->getDomain(...)),
            new TwigFunction('getDomainName', $this->getDomainNameById(...)),
            new TwigFunction('isMultidomain', $this->isMultidomain(...)),
            new TwigFunction('getDomainUrlByLocale', $this->getDomainUrlByLocale(...)),
            new TwigFunction('getFirstDomain', $this->getFirstDomainConfig(...)),
            new TwigFunction('getDomainsCount', $this->getDomainsCount(...)),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'domain';
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getDomainNameById($domainId)
    {
        return $this->getDomain()->getDomainConfigById($domainId)->getName();
    }

    /**
     * @return bool
     */
    public function isMultidomain()
    {
        return $this->getDomain()->isMultidomain();
    }

    public function getDomainUrlByLocale(string $locale): string
    {
        foreach ($this->domain->getAll() as $domain) {
            if ($domain->getLocale() === $locale) {
                return $domain->getUrl();
            }
        }

        throw new NoDomainSelectedException('Domain for locale `' . $locale . '` not found;');
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
