<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Hreflang;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\DataFixtures\Demo\BrandDataFixture;
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
