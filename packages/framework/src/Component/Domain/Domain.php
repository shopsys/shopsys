<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpFoundation\Request;

class Domain implements DomainIdsProviderInterface
{
    public const FIRST_DOMAIN_ID = 1;
    public const SECOND_DOMAIN_ID = 2;
    public const THIRD_DOMAIN_ID = 3;
    public const MAIN_ADMIN_DOMAIN_ID = 1;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig|null
     */
    protected $currentDomainConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    protected $domainConfigs;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(array $domainConfigs, Setting $setting)
    {
        $this->domainConfigs = $domainConfigs;
        $this->setting = $setting;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getCurrentDomainConfig()->getId();
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getCurrentDomainConfig()->getLocale();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getCurrentDomainConfig()->getName();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getCurrentDomainConfig()->getUrl();
    }

    /**
     * @return string|null
     */
    public function getDesignId()
    {
        return $this->getCurrentDomainConfig()->getDesignId();
    }

    /**
     * @return bool
     */
    public function isHttps()
    {
        return $this->getCurrentDomainConfig()->isHttps();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAll()
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
    public function getAllIds()
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
     * @deprecated - will be removed in 9.0 release
     * @return int[]
     */
    public function getAllIdsExcludingFirstDomain(): array
    {
        @trigger_error(sprintf('The method %s::getAllIdsExcludingFirstDomain is deprecated and will be removed in 9.0', __CLASS__), E_USER_DEPRECATED);
        $ids = [];
        foreach ($this->getAll() as $domainConfig) {
            $id = $domainConfig->getId();
            if ($id === self::FIRST_DOMAIN_ID) {
                continue;
            }
            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAllIncludingDomainConfigsWithoutDataCreated()
    {
        return $this->domainConfigs;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getDomainConfigById($domainId)
    {
        foreach ($this->domainConfigs as $domainConfig) {
            if ($domainId === $domainConfig->getId()) {
                return $domainConfig;
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException();
    }

    /**
     * @param int $domainId
     */
    public function switchDomainById($domainId)
    {
        $this->currentDomainConfig = $this->getDomainConfigById($domainId);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function switchDomainByRequest(Request $request)
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

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getCurrentDomainConfig()
    {
        if ($this->currentDomainConfig === null) {
            throw new \Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException();
        }

        return $this->currentDomainConfig;
    }

    /**
     * @return bool
     */
    public function isMultidomain()
    {
        return count($this->getAll()) > 1;
    }
}
