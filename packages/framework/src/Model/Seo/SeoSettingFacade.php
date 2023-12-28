<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class SeoSettingFacade
{
    public const SEO_TITLE_MAIN_PAGE = 'seoTitleMainPage';
    public const SEO_TITLE_ADD_ON = 'seoTitleAddOn';
    public const SEO_META_DESCRIPTION_MAIN_PAGE = 'seoMetaDescriptionMainPage';
    public const SEO_ROBOTS_TXT_CONTENT = 'seoRobotsTxtContent';
    public const SEO_ALTERNATIVE_DOMAINS = 'seoAlternativeDomains';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(protected readonly Setting $setting)
    {
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getTitleMainPage($domainId)
    {
        return $this->setting->getForDomain(self::SEO_TITLE_MAIN_PAGE, $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getDescriptionMainPage($domainId)
    {
        return $this->setting->getForDomain(self::SEO_META_DESCRIPTION_MAIN_PAGE, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     * @return string[]
     */
    public function getDescriptionsMainPageIndexedByDomainIds(array $domainConfigs)
    {
        $descriptionsMainPageByDomainIds = [];

        foreach ($domainConfigs as $domainConfig) {
            $descriptionsMainPageByDomainIds[$domainConfig->getId()] = $this->getDescriptionMainPage(
                $domainConfig->getId(),
            );
        }

        return $descriptionsMainPageByDomainIds;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getTitleAddOn($domainId)
    {
        return $this->setting->getForDomain(self::SEO_TITLE_ADD_ON, $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getRobotsTxtContent(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SEO_ROBOTS_TXT_CONTENT, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setTitleMainPage($value, $domainId)
    {
        $this->setting->setForDomain(self::SEO_TITLE_MAIN_PAGE, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setDescriptionMainPage($value, $domainId)
    {
        $this->setting->setForDomain(self::SEO_META_DESCRIPTION_MAIN_PAGE, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setTitleAddOn($value, $domainId)
    {
        $this->setting->setForDomain(self::SEO_TITLE_ADD_ON, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setRobotsTxtContent(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SEO_ROBOTS_TXT_CONTENT, $value, $domainId);
    }

    /**
     * @param int $domainId
     * @return int[]
     */
    public function getAlternativeDomainsForDomain(int $domainId): array
    {
        $domainJson = $this->setting->get(self::SEO_ALTERNATIVE_DOMAINS);

        $data = $domainJson !== null ? Json::decode($domainJson, Json::FORCE_ARRAY) : [];

        foreach ($data as $group) {
            if (in_array($domainId, $group, true)) {
                return array_diff($group, [$domainId]);
            }
        }

        return [];
    }

    /**
     * @return int[][]
     */
    public function getAllAlternativeDomains(): array
    {
        $dataJson = $this->setting->get(self::SEO_ALTERNATIVE_DOMAINS);

        return $dataJson !== null ? Json::decode($dataJson, Json::FORCE_ARRAY) : [];
    }

    /**
     * @param int[][] $alternativeLanguageDomains
     */
    public function setAllAlternativeDomains(array $alternativeLanguageDomains): void
    {
        $this->setting->set(self::SEO_ALTERNATIVE_DOMAINS, Json::encode($alternativeLanguageDomains));
    }
}
