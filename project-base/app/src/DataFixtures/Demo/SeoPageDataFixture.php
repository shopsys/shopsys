<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageRepository;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageSlugTransformer;

class SeoPageDataFixture extends AbstractReferenceFixture
{
    public const string FIRST_DEMO_SEO_PAGE = 'first_demo_seo_page';

    /**
     * @var array<string, string>
     */
    private static array $demoSeoPages = [
        self::FIRST_DEMO_SEO_PAGE => 'First Demo Seo Page',
    ];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageRepository $seoPageRepository
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade $seoPageFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDataFactory $seoPageDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageSlugTransformer $seoPageSlugTransformer
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly SeoPageRepository $seoPageRepository,
        private readonly SeoPageFacade $seoPageFacade,
        private readonly SeoPageDataFactory $seoPageDataFactory,
        private readonly SeoPageSlugTransformer $seoPageSlugTransformer,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->editPredefinedSeoPages();
        $this->createDemoSeoPages();
    }

    private function createDemoSeoPages(): void
    {
        foreach (self::$demoSeoPages as $pageSlug => $pageName) {
            $seoPageData = $this->seoPageDataFactory->create();
            $seoPageData->pageName = $pageName;

            foreach ($this->domain->getAll() as $domainConfig) {
                $domainId = $domainConfig->getId();

                $seoPageData->pageSlugsIndexedByDomainId[$domainId] = $pageSlug;

                $this->fillSeoPageData(
                    $seoPageData,
                    $domainConfig,
                );
            }

            $seoPage = $this->seoPageFacade->create($seoPageData);

            $this->addReference($pageSlug, $seoPage);
        }
    }

    /**
     * Predefined seo pages are created in database migration.
     *
     * @see \Shopsys\FrameworkBundle\Migrations\Version20240108154625
     */
    private function editPredefinedSeoPages(): void
    {
        $seoPages = $this->seoPageRepository->getAll();

        foreach ($seoPages as $seoPage) {
            $seoPageData = $this->seoPageDataFactory->createFromSeoPage($seoPage);

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
                $this->fillSeoPageData(
                    $seoPageData,
                    $domainConfig,
                    $seoPage->getId(),
                );
            }

            $this->seoPageFacade->edit($seoPage->getId(), $seoPageData);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $seoPageId
     */
    private function fillSeoPageData(
        SeoPageData $seoPageData,
        DomainConfig $domainConfig,
        ?int $seoPageId = null,
    ): void {
        $domainId = $domainConfig->getId();
        $locale = $domainConfig->getLocale();
        $pageName = $seoPageData->pageName;
        $pageSlug = $seoPageData->pageSlugsIndexedByDomainId[$domainId];

        $seoPageSlug = $this->seoPageSlugTransformer->transformFriendlyUrlToSeoPageSlug($pageSlug);

        if ($seoPageSlug === SeoPage::SEO_PAGE_HOMEPAGE_SLUG || $seoPageId === null) {
            $canonicalUrl = $domainConfig->getUrl();
        } else {
            $canonicalUrl = $domainConfig->getUrl() . '/' . $seoPageSlug;
        }

        $seoPageData->seoTitlesIndexedByDomainId[$domainId] = $this->formatAttributeValue($pageName, 'title', $locale);
        $seoPageData->seoMetaDescriptionsIndexedByDomainId[$domainId] = $this->formatAttributeValue($pageName, 'meta description', $locale);
        $seoPageData->seoOgTitlesIndexedByDomainId[$domainId] = $this->formatAttributeValue($pageName, 'og title', $locale);
        $seoPageData->seoOgDescriptionsIndexedByDomainId[$domainId] = $this->formatAttributeValue($pageName, 'og description', $locale);

        $seoPageData->canonicalUrlsIndexedByDomainId[$domainId] = $canonicalUrl;
    }

    /**
     * @param string $pageName
     * @param string $value
     * @param string $locale
     * @return string
     */
    private function formatAttributeValue(string $pageName, string $value, string $locale): string
    {
        return sprintf('%s\'s %s (%s)', $pageName, $value, $locale);
    }
}
