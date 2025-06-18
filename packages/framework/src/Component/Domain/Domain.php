<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use DateTimeZone;
use Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Domain implements DomainIdsProviderInterface
{
    public const int FIRST_DOMAIN_ID = 1;
    public const int SECOND_DOMAIN_ID = 2;
    public const int THIRD_DOMAIN_ID = 3;
    public const int MAIN_ADMIN_DOMAIN_ID = 1;

    protected ?DomainConfig $currentDomainConfig = null;

    protected bool $domainResolvedByFallback = false;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade $administrationFacade
     */
    public function __construct(
        protected readonly array $domainConfigs,
        protected readonly Setting $setting,
        protected readonly AdministratorFacade $administratorFacade,
        protected readonly AdministrationFacade $administrationFacade,
    ) {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->getCurrentDomainConfig()->getId();
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->getCurrentDomainConfig()->getLocale();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getCurrentDomainConfig()->getName();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->getCurrentDomainConfig()->getUrl();
    }

    /**
     * @return bool
     */
    public function isHttps(): bool
    {
        return $this->getCurrentDomainConfig()->isHttps();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAll(): array
    {
        $domainConfigsWithDataCreated = [];

        $this->setting->initAllDomainsSettings();

        foreach ($this->domainConfigs as $domainConfig) {
            $domainId = $domainConfig->getId();

            try {
                $this->setting->getForDomain(Setting::DOMAIN_DATA_CREATED, $domainId);
                $domainConfigsWithDataCreated[$domainId] = $domainConfig;
            } catch (SettingValueNotFoundException $ex) {
                continue;
            }
        }

        return $domainConfigsWithDataCreated;
    }

    /**
     * @return int[]
     */
    public function getAllIds(): array
    {
        $ids = [];

        foreach ($this->getAll() as $domainConfig) {
            $ids[] = $domainConfig->getId();
        }

        return $ids;
    }

    /**
     * @return string[]
     */
    public function getAllLocales(): array
    {
        $locales = [];

        foreach ($this->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $locales[$locale] = $locale;
        }

        return $locales;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAllIncludingDomainConfigsWithoutDataCreated(): array
    {
        return $this->domainConfigs;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getDomainConfigById(int $domainId): DomainConfig
    {
        foreach ($this->domainConfigs as $domainConfig) {
            if ($domainId === $domainConfig->getId()) {
                return $domainConfig;
            }
        }

        throw new InvalidDomainIdException();
    }

    /**
     * @param int $domainId
     */
    public function switchDomainById(int $domainId): void
    {
        $this->currentDomainConfig = $this->getDomainConfigById($domainId);
        $this->domainResolvedByFallback = false;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Component\Router\AdministrationRouter $administrationRouter
     */
    public function switchDomainByRequest(Request $request, AdministrationRouter $administrationRouter): void
    {
        $this->currentDomainConfig = null;

        foreach ($this->domainConfigs as $domainConfig) {
            $this->isDomainWithPostfix($domainConfig);

            if (!str_starts_with($request->getUri(), $domainConfig->getUrl())) {
                continue;
            }

            $this->currentDomainConfig = $domainConfig;
            $this->domainResolvedByFallback = false;
        }

        if ($this->currentDomainConfig === null) {
            d('empty');
            $url = $request->getSchemeAndHttpHost() . $request->getBasePath();
            $firstDomainConfig = $this->getDomainConfigById(self::FIRST_DOMAIN_ID);

            if ($this->shouldFallbackToFirstDomain($firstDomainConfig, $request, $administrationRouter)) {
                $this->currentDomainConfig = $firstDomainConfig;

                $this->domainResolvedByFallback = true;

                return;
            }

            throw new UnableToResolveDomainException($url);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Component\Router\AdministrationRouter $administrationRouter
     * @return bool
     */
    protected function shouldFallbackToFirstDomain(
        DomainConfig $domainConfig,
        Request $request,
        AdministrationRouter $administrationRouter,
    ): bool {
        d('shouldFallbackToFirstDomain');
        if (!$this->isDomainWithPostfix($domainConfig)) {
            return false;
        }

        d($administrationRouter->getRouteCollection());

        try {
            $route = $administrationRouter->match($this->getUriWithTrailingSlash($request))['_route'];
        } catch (ResourceNotFoundException) {
            try {
                $route = $administrationRouter->match($request->getPathInfo())['_route'];
            } catch (ResourceNotFoundException) {
                return false;
            }
        }

        return $route !== false;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    protected function getUriWithTrailingSlash(Request $request): string
    {
        $uri = $request->getPathInfo();

        if (str_ends_with($uri, '/')) {
            return $uri;
        }

        return $uri . '/';
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return bool
     */
    protected function isDomainWithPostfix(DomainConfig $domainConfig): bool
    {
        $path = parse_url($domainConfig->getUrl(), PHP_URL_PATH);

        return $path !== null && $path !== '/';
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getCurrentDomainConfig(): DomainConfig
    {
        if ($this->currentDomainConfig === null) {
            throw new NoDomainSelectedException();
        }

        return $this->currentDomainConfig;
    }

    /**
     * @return bool
     */
    public function isMultidomain(): bool
    {
        return count($this->getAll()) > 1;
    }

    /**
     * @return \DateTimeZone
     */
    public function getDateTimeZone(): DateTimeZone
    {
        return $this->getCurrentDomainConfig()->getDateTimeZone();
    }

    /**
     * @return bool
     */
    public function isB2b(): bool
    {
        return $this->getCurrentDomainConfig()->isB2b();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig|null
     */
    public function findFirstB2bDomain(): ?DomainConfig
    {
        foreach ($this->getAll() as $domainConfig) {
            if ($domainConfig->isB2b()) {
                return $domainConfig;
            }
        }

        return null;
    }

    /**
     * @param int[] $limitDomainsByIds
     * @return int[]
     */
    public function getAdminEnabledDomainIds(array $limitDomainsByIds = []): array
    {
        $selectedDomainIds = $this->administratorFacade->getCurrentlyLoggedAdministrator()->getDisplayOnlyDomainIds();

        $domainIds = count($selectedDomainIds) > 0 ? $selectedDomainIds : $this->getAllIds();

        if (count($limitDomainsByIds) > 0) {
            return array_intersect($domainIds, $limitDomainsByIds);
        }

        return $domainIds;
    }

    /**
     * @param int[] $limitDomainsByIds
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAdminEnabledDomains(array $limitDomainsByIds = []): array
    {
        $domains = [];

        foreach ($this->getAdminEnabledDomainIds($limitDomainsByIds) as $selectedDomainId) {
            $domains[$selectedDomainId] = $this->getDomainConfigById($selectedDomainId);
        }

        return $domains;
    }

    /**
     * @return bool
     */
    public function hasAdminAllDomainsEnabled(): bool
    {
        return count($this->getAdminEnabledDomainIds()) === count($this->getAllIds());
    }

    /**
     * @return bool
     */
    public function isDomainResolvedByFallback(): bool
    {
        return $this->domainResolvedByFallback;
    }

    /**
     * @return string|null
     */
    public function getPostfix(): ?string
    {
        return $this->getCurrentDomainConfig()->getPostfix();
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->getCurrentDomainConfig()->getBaseUrl();
    }
}
