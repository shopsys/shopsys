<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use DateTimeZone;
use Override;
use Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Component\HttpFoundation\Request;

class Domain implements DomainIdsProviderInterface
{
    public const int FIRST_DOMAIN_ID = 1;
    public const int SECOND_DOMAIN_ID = 2;
    public const int THIRD_DOMAIN_ID = 3;

    protected ?DomainConfig $currentDomainConfig = null;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     */
    public function __construct(
        protected readonly array $domainConfigs,
        protected readonly Setting $setting,
        protected readonly CurrentAdministrator $currentAdministrator,
    ) {
    }

    public function getId(): int
    {
        return $this->getCurrentDomainConfig()->getId();
    }

    public function getLocale(): string
    {
        return $this->getCurrentDomainConfig()->getLocale();
    }

    public function getName(): string
    {
        return $this->getCurrentDomainConfig()->getName();
    }

    public function getUrl(): string
    {
        return $this->getCurrentDomainConfig()->getUrl();
    }

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
    #[Override]
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

    public function getDomainConfigById(int $domainId): DomainConfig
    {
        foreach ($this->domainConfigs as $domainConfig) {
            if ($domainId === $domainConfig->getId()) {
                return $domainConfig;
            }
        }

        throw new InvalidDomainIdException();
    }

    public function switchDomainById(int $domainId): void
    {
        $this->currentDomainConfig = $this->getDomainConfigById($domainId);
    }

    public function switchDomainByRequest(Request $request): void
    {
        $requestBaseUrl = $request->getSchemeAndHttpHost();
        $requestPath = '/' . trim($request->getPathInfo(), '/');

        foreach ($this->getDomainConfigsSortedFromLongestPostfix($requestBaseUrl) as $domainConfig) {
            $postfix = '/' . trim($domainConfig->getPostfix() ?? '', '/');

            if (!$this->pathMatchesDomainPostfix($requestPath, $postfix)) {
                continue;
            }

            $this->currentDomainConfig = $domainConfig;

            return;
        }

        throw new UnableToResolveDomainException($request->getUri());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    protected function getDomainConfigsSortedFromLongestPostfix(string $requestBaseUrl): array
    {
        $matchingConfigs = [];

        foreach ($this->domainConfigs as $domainConfig) {
            if ($domainConfig->getBaseUrl() === $requestBaseUrl) {
                $matchingConfigs[] = $domainConfig;
            }
        }

        usort($matchingConfigs, static function (DomainConfig $a, DomainConfig $b) {
            $aPostfixLength = strlen($a->getPostfix() ?? '');
            $bPostfixLength = strlen($b->getPostfix() ?? '');

            return $bPostfixLength - $aPostfixLength;
        });

        return $matchingConfigs;
    }

    protected function pathMatchesDomainPostfix(string $requestPath, string $postfix): bool
    {
        if ($postfix === '/') {
            return true;
        }

        return $requestPath === $postfix || str_starts_with($requestPath, $postfix . '/');
    }

    public function getCurrentDomainConfig(): DomainConfig
    {
        if ($this->currentDomainConfig === null) {
            throw new NoDomainSelectedException();
        }

        return $this->currentDomainConfig;
    }

    public function isMultidomain(): bool
    {
        return count($this->getAll()) > 1;
    }

    public function getDateTimeZone(): DateTimeZone
    {
        return $this->getCurrentDomainConfig()->getDateTimeZone();
    }

    public function isB2b(): bool
    {
        return $this->getCurrentDomainConfig()->isB2b();
    }

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
    #[Override]
    public function getAdminEnabledDomainIds(array $limitDomainsByIds = []): array
    {
        $selectedDomainIds = $this->currentAdministrator->getCurrentlyLoggedAdministrator()->getDisplayOnlyDomainIds();

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

    public function hasAdminAllDomainsEnabled(): bool
    {
        return count($this->getAdminEnabledDomainIds()) === count($this->getAllIds());
    }

    public function getPostfix(): ?string
    {
        return $this->getCurrentDomainConfig()->getPostfix();
    }

    public function getBaseUrl(): string
    {
        return $this->getCurrentDomainConfig()->getBaseUrl();
    }

    public function getFirstDomainIdMatchingAdminSelectedLocale(Administrator $administrator): int
    {
        $adminLocale = $administrator->getSelectedLocale();

        foreach ($this->getAll() as $domainConfig) {
            if ($domainConfig->getLocale() === $adminLocale) {
                return $domainConfig->getId();
            }
        }

        return static::FIRST_DOMAIN_ID;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAllWithSameBaseUrl(DomainConfig $inputDomainConfig): array
    {
        $domainsWithSameBaseUrl = [];

        foreach ($this->getAll() as $domainConfig) {
            if ($domainConfig->getBaseUrl() === $inputDomainConfig->getBaseUrl()) {
                $domainsWithSameBaseUrl[] = $domainConfig;
            }
        }

        return $domainsWithSameBaseUrl;
    }

    public function isMainDomainWithinSameBaseUrlGroup(DomainConfig $domainConfig): bool
    {
        if ($domainConfig->getPostfix() === null) {
            return true;
        }

        $domainsWithSameBaseUrl = $this->getAllWithSameBaseUrl($domainConfig);

        if (count($domainsWithSameBaseUrl) === 1) {
            return true;
        }

        foreach ($domainsWithSameBaseUrl as $domain) {
            if ($domain->getId() < $domainConfig->getId()) {
                return false;
            }
        }

        return true;
    }

    public function anyDomainHasLocale(string $locale): bool
    {
        return in_array($locale, $this->getAllLocales(), true);
    }
}
