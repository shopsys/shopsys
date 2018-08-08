<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpFoundation\Request;

class Domain implements DomainIdsProviderInterface
{
    const FIRST_DOMAIN_ID = 1;
    const MAIN_ADMIN_DOMAIN_ID = 1;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig|null
     */
    private $currentDomainConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private $domainConfigs;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     */
    public function __construct(array $domainConfigs, Setting $setting)
    {
        $this->domainConfigs = $domainConfigs;
        $this->setting = $setting;
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
        foreach ($this->domainConfigs as $domainConfig) {
            $domainId = $domainConfig->getId();
            try {
                $this->setting->getForDomain(Setting::DOMAIN_DATA_CREATED, $domainId);
                $domainConfigsWithDataCreated[] = $domainConfig;
            } catch (\Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException $ex) {
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
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAllIncludingDomainConfigsWithoutDataCreated(): array
    {
        return $this->domainConfigs;
    }
    
    public function getDomainConfigById(int $domainId): \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
    {
        foreach ($this->domainConfigs as $domainConfig) {
            if ($domainId === $domainConfig->getId()) {
                return $domainConfig;
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException();
    }
    
    public function switchDomainById(int $domainId): void
    {
        $this->currentDomainConfig = $this->getDomainConfigById($domainId);
    }

    public function switchDomainByRequest(Request $request): void
    {
        // Request::getBasePath() never contains script file name (/index.php)
        $url = $request->getSchemeAndHttpHost() . $request->getBasePath();

        foreach ($this->domainConfigs as $domainConfig) {
            if ($domainConfig->getUrl() === $url) {
                $this->currentDomainConfig = $domainConfig;
                return;
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException($url);
    }

    public function getCurrentDomainConfig(): \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
    {
        if ($this->currentDomainConfig === null) {
            throw new \Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException();
        }

        return $this->currentDomainConfig;
    }

    public function isMultidomain(): bool
    {
        return count($this->getAll()) > 1;
    }
}
