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
     * TODO find usages and add a new method or something like getAdminLocaleWithFallback(int $fallbackDomainId = 1)
     *
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
     * The method is handy when you need to get the entity translations in the admin locale.
     * It has a safety net in form of a fallback domain ID that ensures the method always returns a valid locale from the entity translations point of view.
     * The fallback is necessary for setup with the admin locale is different from the storefront domain locales.
     *
     * @param int $fallbackLocaleDomainId
     * @return string
     */
    public function getAdminLocaleWithFallback(int $fallbackLocaleDomainId = Domain::FIRST_DOMAIN_ID): string
    {
        $adminLocale = $this->getAdminLocale();

        if (!in_array($adminLocale, $this->getLocalesOfAllDomains(), true)) {
            $adminLocale = $this->domain->getDomainConfigById($fallbackLocaleDomainId)->getLocale();
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
