<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageRepository;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageSlugTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly SeoPageRepository $seoPageRepository,
        private readonly SeoPageFacade $seoPageFacade,
        private readonly SeoPageDataFactory $seoPageDataFactory,
        private readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
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
                $locale = $domainConfig->getLocale();

                $seoPageData->pageSlugsIndexedByDomainId[$domainId] = $pageSlug;

                $this->fillSeoPageData(
                    $seoPageData,
                    $domainId,
                    $locale,
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
                    $domainConfig->getId(),
                    $domainConfig->getLocale(),
                    $seoPage->getId(),
                );
            }

            $this->seoPageFacade->edit($seoPage->getId(), $seoPageData);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     * @param int $domainId
     * @param string $locale
     * @param int|null $seoPageId
     */
    private function fillSeoPageData(
        SeoPageData $seoPageData,
        int $domainId,
        string $locale,
        ?int $seoPageId = null,
    ): void {
        $seoPageDomainRouter = $this->domainRouterFactory->getRouter($domainId);

        $pageName = $seoPageData->pageName;
        $pageSlug = $seoPageData->pageSlugsIndexedByDomainId[$domainId];

        $seoPageSlug = SeoPageSlugTransformer::transformFriendlyUrlToSeoPageSlug($pageSlug);

        if ($seoPageSlug === SeoPage::SEO_PAGE_HOMEPAGE_SLUG || $seoPageId === null) {
            $canonicalUrl = $seoPageDomainRouter->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $canonicalUrl = $seoPageDomainRouter->generate('front_page_seo', [
                'id' => $seoPageId,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
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
