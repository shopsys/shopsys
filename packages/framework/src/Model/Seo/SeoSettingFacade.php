<?php

namespace Shopsys\FrameworkBundle\Model\Seo;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class SeoSettingFacade
{
    public const SEO_TITLE_MAIN_PAGE = 'seoTitleMainPage';
    public const SEO_TITLE_ADD_ON = 'seoTitleAddOn';
    public const SEO_META_DESCRIPTION_MAIN_PAGE = 'seoMetaDescriptionMainPage';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getTitleMainPage(int $domainId): string
    {
        return $this->setting->getForDomain(self::SEO_TITLE_MAIN_PAGE, $domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getDescriptionMainPage(int $domainId): string
    {
        return $this->setting->getForDomain(self::SEO_META_DESCRIPTION_MAIN_PAGE, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     * @return string[]
     */
    public function getDescriptionsMainPageIndexedByDomainIds(array $domainConfigs): array
    {
        $descriptionsMainPageByDomainIds = [];
        foreach ($domainConfigs as $domainConfig) {
            $descriptionsMainPageByDomainIds[$domainConfig->getId()] = $this->getDescriptionMainPage(
                $domainConfig->getId()
            );
        }

        return $descriptionsMainPageByDomainIds;
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getTitleAddOn(int $domainId): string
    {
        return $this->setting->getForDomain(self::SEO_TITLE_ADD_ON, $domainId);
    }

    /**
     * @param string $value
     * @param int $domainId
     */
    public function setTitleMainPage(string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SEO_TITLE_MAIN_PAGE, $value, $domainId);
    }

    /**
     * @param string $value
     * @param int $domainId
     */
    public function setDescriptionMainPage(string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SEO_META_DESCRIPTION_MAIN_PAGE, $value, $domainId);
    }

    /**
     * @param string $value
     * @param int $domainId
     */
    public function setTitleAddOn(string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SEO_TITLE_ADD_ON, $value, $domainId);
    }
}
