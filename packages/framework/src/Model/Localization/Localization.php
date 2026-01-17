<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Locale;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NoRequestException;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Symfony\Component\HttpFoundation\RequestStack;

class Localization
{
    /**
     * @var string[]|null
     */
    protected ?array $allLocales = null;

    /**
     * @param string[] $allowedAdminLocales
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly array $allowedAdminLocales,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * The proper locale is set into request in @see \Shopsys\FrameworkBundle\Model\Localization\LocalizationListener::onKernelRequest()
     */
    public function getRequestLocale(): string
    {
        $request = $this->requestStack->getMainRequest();

        if ($request === null) {
            throw new NoRequestException('Request is not available.');
        }

        return $request->getLocale();
    }

    /**
     * The method is handy when you need to get the entity translations in the admin locale.
     * It has a safety net in form of a fallback domain ID that ensures the method always returns a valid locale from the entity translations point of view.
     * The fallback is necessary for setup with the admin locale is different from the storefront domain locales.
     */
    public function getCurrentLocaleForTranslatableEntities(
        int $fallbackLocaleDomainId = Domain::FIRST_DOMAIN_ID,
    ): string {
        $requestLocale = $this->getRequestLocale();

        return $this->getFallbackLocaleIfLocaleIsNotUsedOnAnyDomain($requestLocale, $fallbackLocaleDomainId);
    }

    public function getFallbackLocaleIfLocaleIsNotUsedOnAnyDomain(
        string $locale,
        int $fallbackLocaleDomainId = Domain::FIRST_DOMAIN_ID,
    ): string {
        if (!in_array($locale, $this->getLocalesOfAllDomains(), true)) {
            return $this->domain->getDomainConfigById($fallbackLocaleDomainId)->getLocale();
        }

        return $locale;
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

    public function getLanguageName(string $locale, ?string $displayLocale = null): string
    {
        return Locale::getDisplayLanguage($locale, $displayLocale);
    }

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
