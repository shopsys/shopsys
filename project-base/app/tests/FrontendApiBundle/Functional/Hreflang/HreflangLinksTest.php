<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Hreflang;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\DataFixtures\Demo\BlogArticleDataFixture;
use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class HreflangLinksTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @inject
     */
    private SeoSettingFacade $seoSettingFacade;

    /**
     * @return iterable
     */
    public function getHreflangEntitiesDataProvider(): iterable
    {
        yield 'Brand' => [
            'entityReference' => BrandDataFixture::BRAND_APPLE,
            'routeName' => 'front_brand_detail',
            'graphQlFileName' => 'BrandHreflangLinksQuery.graphql',
            'entityName' => 'brand',
        ];

        yield 'Category' => [
            'entityReference' => CategoryDataFixture::CATEGORY_BOOKS,
            'routeName' => 'front_product_list',
            'graphQlFileName' => 'CategoryHreflangLinksQuery.graphql',
            'entityName' => 'category',
        ];

        yield 'Product' => [
            'entityReference' => ProductDataFixture::PRODUCT_PREFIX . 1,
            'routeName' => 'front_product_detail',
            'graphQlFileName' => 'ProductHreflangLinksQuery.graphql',
            'entityName' => 'product',
        ];

        yield 'BlogArticle' => [
            'entityReference' => BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE,
            'routeName' => 'front_blogarticle_detail',
            'graphQlFileName' => 'BlogArticleHreflangLinksQuery.graphql',
            'entityName' => 'blogArticle',
        ];

        yield 'BlogCategory' => [
            'entityReference' => BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY,
            'routeName' => 'front_blogcategory_detail',
            'graphQlFileName' => 'BlogCategoryHreflangLinksQuery.graphql',
            'entityName' => 'blogCategory',
        ];
    }

    /**
     * @dataProvider getHreflangEntitiesDataProvider
     * @param string $entityReference
     * @param string $routeName
     * @param string $graphQlFileName
     * @param string $graphQlType
     */
    public function testNoAlternateReturnsOnlyItself(
        string $entityReference,
        string $routeName,
        string $graphQlFileName,
        string $graphQlType,
    ): void {
        $this->seoSettingFacade->setAllAlternativeDomains([]);

        $entity = $this->getReference($entityReference);

        if ($graphQlType === 'product') {
            $this->handleDispatchedRecalculationMessages([$entity->getId()]);

            // Wait for elasticsearch to index the product
            sleep(1);
        }

        if ($graphQlType === 'blogArticle') {
            $this->markTestSkipped('Cron module export changed has to be run before to obtain proper data. This test may be enabled after blog articles are exported via queue.');
        }

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/' . $graphQlFileName,
            [
                'urlSlug' => $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
                    $this->domain->getId(),
                    $routeName,
                    $entity->getId(),
                ),
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $expected = [
            'hreflangLinks' => [
                [
                    'hreflang' => $this->getFirstDomainLocale(),
                    'href' => $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                        $this->domain->getId(),
                        $routeName,
                        $entity->getId(),
                    ),
                ],
            ],
        ];

        self::assertEquals($expected, $data);
    }

    /**
     * @group multidomain
     * @dataProvider getHreflangEntitiesDataProvider
     * @param string $entityReference
     * @param string $routeName
     * @param string $graphQlFileName
     * @param string $graphQlType
     */
    public function testAlternateDomainLanguages(
        string $entityReference,
        string $routeName,
        string $graphQlFileName,
        string $graphQlType,
    ): void {
        $entity = $this->getReference($entityReference);
        $secondDomainId = 2;

        if ($graphQlType === 'blogArticle') {
            $this->markTestSkipped('Cron module export changed has to be run before to obtain proper data. This test may be enabled after blog articles are exported via queue.');
        }

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/' . $graphQlFileName,
            [
                'urlSlug' => $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
                    $this->domain->getId(),
                    $routeName,
                    $entity->getId(),
                ),
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $expected = [
            'hreflangLinks' => [
                [
                    'hreflang' => $this->getFirstDomainLocale(),
                    'href' => $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                        $this->domain->getId(),
                        $routeName,
                        $entity->getId(),
                    ),
                ],
                [
                    'hreflang' => $this->domain->getDomainConfigById($secondDomainId)->getLocale(),
                    'href' => $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                        $secondDomainId,
                        $routeName,
                        $entity->getId(),
                    ),
                ],
            ],
        ];

        self::assertEquals($expected, $data);
    }
}
