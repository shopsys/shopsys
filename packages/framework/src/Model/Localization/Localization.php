<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Locale;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Exception\AdminLocaleNotFoundException;

class Localization
{
    /**
     * @var string[]|null
     */
    protected ?array $allLocales = null;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param string $adminLocale
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly string $adminLocale,
    ) {
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->domain->getLocale();
    }

    /**
     * @return string
     */
    public function getAdminLocale(): string
    {
        $allLocales = $this->getLocalesOfAllDomains();

        if (!in_array($this->adminLocale, $allLocales, true)) {
            throw new AdminLocaleNotFoundException($this->adminLocale, $allLocales);
        }

        return $this->adminLocale;
    }

    /**
     * @return string[]
     */
    public function getLocalesOfAllDomains(): array
    {
        if ($this->allLocales === null) {
            $this->allLocales = $this->domain->getAllLocales();
        }

        return $this->allLocales;
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getLanguageName(string $locale): string
    {
        return Locale::getDisplayLanguage($locale);
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getCollationByLocale(string $locale): string
    {
        return $locale . '-x-icu';
    }

    /**
     * @return string[]
     */
    public function getAdminEnabledLocales(): array
    {
        $enabledLocales = [];

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            $enabledLocales[] = $domainConfig->getLocale();
        }

        return $enabledLocales;
    }
}
