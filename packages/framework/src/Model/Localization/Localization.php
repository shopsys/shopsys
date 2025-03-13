<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Locale;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Localization\Exception\AdminLocaleNotFoundException;

class Localization
{
    /**
     * @var string[]|null
     */
    protected ?array $allLocales = null;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param string[] $allowedAdminLocales
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly array $allowedAdminLocales,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
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
        try {
            $adminLocale = $this->administratorFrontSecurityFacade->getCurrentAdministrator()->getSelectedLocale();
            $this->checkAdminLocaleIsSupported($adminLocale);
        } catch (AdministratorIsNotLoggedException) {
            $adminLocale = $this->getDefaultAdminLocale();
        }

        return $adminLocale;
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
     * @param string|null $displayLocale
     * @return string
     */
    public function getLanguageName(string $locale, ?string $displayLocale = null): string
    {
        return Locale::getDisplayLanguage($locale, $displayLocale);
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

    /**
     * @param string $locale
     */
    public function checkAdminLocaleIsSupported(string $locale): void
    {
        $allLocales = $this->getLocalesOfAllDomains();

        if (!in_array($locale, $allLocales, true)) {
            throw new AdminLocaleNotFoundException($locale, $allLocales);
        }

        if (!in_array($locale, $this->allowedAdminLocales, true)) {
            throw new AdminLocaleNotFoundException($locale, $this->allowedAdminLocales);
        }
    }

    /**
     * @return string
     */
    public function getDefaultAdminLocale(): string
    {
        $allowedAdminLocales = $this->allowedAdminLocales;
        $defaultAdminLocale = reset($allowedAdminLocales);

        if ($defaultAdminLocale === false) {
            throw new AdminLocaleNotFoundException();
        }

        $this->checkAdminLocaleIsSupported($defaultAdminLocale);

        return $defaultAdminLocale;
    }

    /**
     * @return string[]
     */
    public function getAllowedAdminLocales(): array
    {
        return $this->allowedAdminLocales;
    }
}
