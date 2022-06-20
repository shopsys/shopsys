<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category\ReadyCategorySeoMix;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use App\DataFixtures\Demo\ReadyCategorySeoDataFixture;
use App\Model\Product\Parameter\ParameterFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ReadyCategorySeoMixTest extends GraphQlTestCase
{
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     * @inject
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     * @inject
     */
    private ParameterFacade $parameterFacade;

    public function testGetReadyCategorySeoMixDataBySlug()
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix */
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_ELECTRONICS_WITHOUT_HDMI, 1);
        $query = '
            query slug {
                slug(slug: "elektro-bez-hdmi") {
                    ... on Category {
                        name
                        slug
                        seoH1
                        seoTitle
                        seoMetaDescription
                        bestsellers {
                            name
                        }
                        breadcrumb {
                            name
                            slug
                        }
                        readyCategorySeoMixLinks {
                            name
                            slug
                        }
                        linkedCategories {
                            name
                        }
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'slug' => [
                    'name' => t('Electronics', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'slug' => '/elektro-bez-hdmi',
                    'seoH1' => 'Elektro bez HDMI',
                    'seoTitle' => 'Elektro bez HDMI',
                    'seoMetaDescription' => t(
                        'All kind of electronic devices.',
                        [],
                        'dataFixtures',
                        $this->getLocaleForFirstDomain()
                    ),
                    'bestsellers' => [
                        ['name' => t('47" LG 47LA790V (FHD)', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('32" Philips 32PFL4308', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                    ],
                    'breadcrumb' => [
                        [
                            'name' => t('Electronics', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $readyCategorySeoMix->getCategory()->getId()]),
                        ],
                    ],
                    'readyCategorySeoMixLinks' => [
                        ['name' => 'Elektro Akce - od nejlevnějšího - 47 - bez hdmi', 'slug' => 'elektro-akce-od-nejlevnejsiho-47-bez-hdmi'],
                        ['name' => 'Elektro bez HDMI', 'slug' => 'elektro-bez-hdmi'],
                        ['name' => 'Elektro nejprodávanější - A-Z - 27" - bez HDMI', 'slug' => 'elektro-nejprodavanejsi-a-z-27-bez-hdmi'],
                        ['name' => 'Elektro Novinky - TOP - 27" - HDMI', 'slug' => 'elektro-novinky-top-27-hdmi'],
                        ['name' => 'Elektro s HDMI', 'slug' => 'elektro-s-hdmi'],
                    ],
                    'linkedCategories' => [
                        ['name' => t('Food', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('Garden tools', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testReadyCategorySeoMixProductsOrdering()
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix */
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_FROM_CHEAPEST, 1);
        $urlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoMix->getId()]);
        $query = '
            query slug {
                slug(slug: "' . $urlSlug . '") {
                    ... on Category {
                        products(first:1) {
                            orderingMode
                            edges {
                                node {
                                  name
                                }
                            }
                        }
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'slug' => [
                    'products' => [
                        'orderingMode' => 'PRICE_ASC',
                        'edges' => [
                            ['node' => ['name' => t('Defender 2.0 SPK-480', [], 'dataFixtures', $this->getLocaleForFirstDomain())]],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testReadyCategorySeoMixProductsWithFlag()
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix */
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_IN_SALE, 1);
        $urlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoMix->getId()]);
        $query = $this->getSlugQueryForCategoryWithProductNames($urlSlug);

        $arrayExpected = [
            'data' => [
                'slug' => [
                    'products' => [
                        'orderingMode' => 'PRIORITY',
                        'edges' => [
                            ['node' => ['name' => t('Philips 32PFL4308', [], 'dataFixtures', $this->getLocaleForFirstDomain())]],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testReadyCategorySeoMixProductsWithParameters()
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix */
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_PLASMA_WITH_HDMI, 1);
        $urlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoMix->getId()]);
        $query = $this->getSlugQueryForCategoryWithProductNames($urlSlug);

        $arrayExpected = [
            'data' => [
                'slug' => [
                    'products' => [
                        'orderingMode' => 'PRIORITY',
                        'edges' => [
                            ['node' => ['name' => t('Hyundai 32PFL4400', [], 'dataFixtures', $this->getLocaleForFirstDomain())]],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    /**
     * @param string $urlSlug
     * @return string
     */
    private function getSlugQueryForCategoryWithProductNames(string $urlSlug): string
    {
        return '
            query slug {
                slug(slug: "' . $urlSlug . '") {
                    ... on Category {
                        products {
                        orderingMode
                            edges {
                                node {
                                  name
                                }
                            }
                        }
                    }
                }
            }
        ';
    }

    public function testReadyCategorySeoMixReturnsSelectedFilterOptions(): void
    {
        $data = $this->getDataForCategorySeoMix(__DIR__ . '/graphql/ReadyCategorySeoMixQuery.graphql');

        $this->assertSelectedFlags($data['products']['productFilterOptions']['flags']);
        $this->assertSelectedParameterCheckboxFilterOptions($data['products']['productFilterOptions']['parameters']);
        $this->assertSelectedParameterSliderFilterOptions($data['products']['productFilterOptions']['parameters']);
    }

    public function testReadyCategorySeoMixDataAreReturnedWhenMatchedFromCategory(): void
    {
        $dataForCategorySeoMix = $this->getDataForCategorySeoMix(__DIR__ . '/graphql/SlugQueryCategoryMatchingSeoMix.graphql');
        $dataForCategory = $this->getDataForCategoryWithFiltersMatchingSeoMix();

        $this->assertSame($dataForCategorySeoMix, $dataForCategory);
    }

    /**
     * @param array $flags
     */
    private function assertSelectedFlags(array $flags): void
    {
        /** @var \App\Model\Product\Flag\Flag $newFlag */
        $newFlag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        foreach ($flags as $flagData) {
            if ($flagData['flag']['uuid'] === $newFlag->getUuid()) {
                $this->assertTrue($flagData['isSelected']);
            } else {
                $this->assertFalse($flagData['isSelected']);
            }
        }
    }

    /**
     * @param array $parameters
     */
    private function assertSelectedParameterCheckboxFilterOptions(array $parameters): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        /** @var \App\Model\Product\Parameter\Parameter $usbParameter */
        $usbParameter = $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('USB', [], 'dataFixtures', $firstDomainLocale));
        $yesValue = t('Yes', [], 'dataFixtures', $firstDomainLocale);

        foreach ($parameters as $parameterData) {
            if ($parameterData['uuid'] === $usbParameter->getUuid()) {
                foreach ($parameterData['values'] as $valueData) {
                    if ($valueData['text'] === $yesValue) {
                        $this->assertTrue($valueData['isSelected']);
                    } else {
                        $this->assertFalse($valueData['isSelected']);
                    }
                }
            } elseif ($parameterData['__typename'] === 'ParameterCheckboxFilterOption') {
                foreach ($parameterData['values'] as $valueData) {
                    $this->assertFalse($valueData['isSelected']);
                }
            }
        }
    }

    /**
     * @param array $parameters
     */
    private function assertSelectedParameterSliderFilterOptions(array $parameters): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        /** @var \App\Model\Product\Parameter\Parameter $warrantyParameter */
        $warrantyParameter = $this->getReference(ParameterDataFixture::PARAMETER_SLIDER_WARRANTY);
        $fourValue = t('4', [], 'dataFixtures', $firstDomainLocale);

        foreach ($parameters as $parameterData) {
            if ($parameterData['uuid'] === $warrantyParameter->getUuid()) {
                $this->assertSame($fourValue, (string)$parameterData['selectedValue']);
            }
        }
    }

    /**
     * @param string $graphQlFilePath
     * @return array
     */
    private function getDataForCategorySeoMix(string $graphQlFilePath): array
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoPcNewWithUsb */
        $readyCategorySeoPcNewWithUsb = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_PC_NEW_WITH_USB, 1);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoPcNewWithUsb->getId()]);
        $responseForSeoMix = $this->getResponseContentForGql($graphQlFilePath, [
            'slug' => $seoMixUrlSlug,
        ]);

        return $this->getResponseDataForGraphQlType($responseForSeoMix, 'slug');
    }

    /**
     * @return array
     */
    private function getDataForCategoryWithFiltersMatchingSeoMix(): array
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        /** @var \App\Model\Category\Category $categoryPc */
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC);
        /** @var \App\Model\Product\Flag\Flag $flagNew */
        $flagNew = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        /** @var \App\Model\Product\Parameter\Parameter $parameterUsb */
        $parameterUsb = $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('USB', [], 'dataFixtures', $firstDomainLocale));
        /** @var \App\Model\Product\Parameter\Parameter $parameterWarranty */
        $parameterWarranty = $this->getReference(ParameterDataFixture::PARAMETER_SLIDER_WARRANTY);
        $categorySlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);
        $valueFour = t('4', [], 'dataFixtures', $firstDomainLocale);
        $parameterValueYes = $this->parameterFacade->getParameterValueByValueTextAndLocale(
            t('Yes', [], 'dataFixtures', $firstDomainLocale),
            $firstDomainLocale
        );
        $responseForCategory = $this->getResponseContentForGql(__DIR__ . '/graphql/SlugQueryCategoryMatchingSeoMix.graphql', [
            'slug' => $categorySlug,
            'sortingMode' => 'PRICE_DESC',
            'filter' => [
                'flags' => [$flagNew->getUuid()],
                'parameters' => [
                    [
                        'parameter' => $parameterWarranty->getUuid(),
                        'minimalValue' => $valueFour,
                        'maximalValue' => $valueFour,
                    ], [
                        'parameter' => $parameterUsb->getUuid(),
                        'values' => [
                            $parameterValueYes->getUuid(),
                        ],
                    ],
                ],
            ],
        ]);

        return  $this->getResponseDataForGraphQlType($responseForCategory, 'slug');
    }
}
