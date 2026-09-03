<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class SeoSettingFacade
{
    public const SEO_TITLE_MAIN_PAGE = 'seoTitleMainPage';
    public const SEO_TITLE_ADD_ON = 'seoTitleAddOn';
    public const SEO_META_DESCRIPTION_MAIN_PAGE = 'seoMetaDescriptionMainPage';
    public const SEO_ROBOTS_TXT_CONTENT = 'seoRobotsTxtContent';
    public const SEO_ALTERNATIVE_DOMAINS = 'seoAlternativeDomains';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly BlogArticleExportQueueFacade $blogArticleExportQueueFacade,
    ) {
    }

    public function getTitleMainPage(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SEO_TITLE_MAIN_PAGE, $domainId);
    }

    public function getDescriptionMainPage(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SEO_META_DESCRIPTION_MAIN_PAGE, $domainId);
    }

    public function getTitleAddOn(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SEO_TITLE_ADD_ON, $domainId);
    }

    public function getRobotsTxtContent(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SEO_ROBOTS_TXT_CONTENT, $domainId);
    }

    public function setTitleMainPage(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SEO_TITLE_MAIN_PAGE, $value, $domainId);
    }

    public function setDescriptionMainPage(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SEO_META_DESCRIPTION_MAIN_PAGE, $value, $domainId);
    }

    public function setTitleAddOn(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SEO_TITLE_ADD_ON, $value, $domainId);
    }

    public function setRobotsTxtContent(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SEO_ROBOTS_TXT_CONTENT, $value, $domainId);
    }

    /**
     * @return int[]
     */
    public function getAlternativeDomainsForDomain(int $domainId): array
    {
        $domainJson = $this->setting->get(self::SEO_ALTERNATIVE_DOMAINS);

        $data = $domainJson !== null ? Json::decode($domainJson, true) : [];

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

        return $dataJson !== null ? Json::decode($dataJson, true) : [];
    }

    /**
     * @param int[][] $alternativeLanguageDomains
     */
    public function setAllAlternativeDomains(array $alternativeLanguageDomains): void
    {
        $this->setting->set(self::SEO_ALTERNATIVE_DOMAINS, Json::encode($alternativeLanguageDomains));

        $this->productRecalculationDispatcher->dispatchAllProducts();

        foreach ($alternativeLanguageDomains as $alternativeLanguageDomain) {
            foreach ($alternativeLanguageDomain as $domainId) {
                $this->blogArticleExportQueueFacade->addAll($domainId);
            }
        }
    }
}
