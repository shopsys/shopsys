<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category\ReadyCategorySeoMix;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use App\DataFixtures\Demo\ReadyCategorySeoDataFixture;
use App\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorter;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ReadyCategorySeoMixTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    private ParameterFacade $parameterFacade;

    public function testGetReadyCategorySeoMixDataBySlug()
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix */
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_ELECTRONICS_WITHOUT_HDMI_PROMOTION, 1);
        $query = '
            query slug {
                slug(slug: "elektro-bez-hdmi-akce") {
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

        $readyCategorySeoMixLinks = [
            [
                'name' => t('Electronics from most expensive', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-od-nejdrazsiho',
            ],
            [
                'name' => t('Electronics in black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-barva-cerna',
            ],
            [
                'name' => t('Electronics in red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-barva-cervena',
            ],
            [
                'name' => t('Electronics with LED technology and size 30 inch in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-led-uhlopricka-30-akce',
            ],
            [
                'name' => t('Electronics without HDMI in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-bez-hdmi-akce',
            ],
            [
                'name' => t('Full HD Electronics with LED technology and USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-full-hd-led-usb',
            ],
        ];

        ArraySorter::sortArrayAlphabeticallyByValue('name', $readyCategorySeoMixLinks, $this->getLocaleForFirstDomain());

        $arrayExpected = [
            'data' => [
                'slug' => [
                    'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'slug' => '/elektro-bez-hdmi-akce',
                    'seoH1' => t('Electronics without HDMI in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'seoTitle' => t('Electronics without HDMI in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'seoMetaDescription' => t('All kind of electronic devices.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    'bestsellers' => [
                        ['name' => t('47" LG 47LA790V (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ['name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ['name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ],
                    'breadcrumb' => [
                        [
                            'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                            'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $readyCategorySeoMix->getCategory()->getId()]),
                        ],
                    ],
                    'readyCategorySeoMixLinks' => $readyCategorySeoMixLinks,
                    'linkedCategories' => [
                        ['name' => t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                        ['name' => t('Garden tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
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
                            defaultOrderingMode
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
                        'defaultOrderingMode' => 'PRICE_ASC',
                        'edges' => [
                            ['node' => ['name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())]],
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
                        'defaultOrderingMode' => 'PRIORITY',
                        'edges' => [
                            ['node' => ['name' => t('Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())]],
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
                        'defaultOrderingMode' => 'PRIORITY',
                        'edges' => [
                            ['node' => ['name' => t('32" Hyundai 32PFL4400', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())]],
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
                            defaultOrderingMode
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
        $data = $this->getDataForCategorySeoMixPcNewWithUsb(__DIR__ . '/../../_graphql/query/ReadyCategorySeoMixQuery.graphql');

        $this->assertSelectedFlags($data['products']['productFilterOptions']['flags']);
        $this->assertSelectedParameterCheckboxFilterOptions($data['products']['productFilterOptions']['parameters']);
    }

    public function testReadyCategorySeoMixDataAreReturnedWhenMatchedFromCategory(): void
    {
        $dataForCategorySeoMix = $this->getDataForCategorySeoMixPcNewWithUsb(__DIR__ . '/../../_graphql/query/SlugQueryCategoryMatchingSeoMix.graphql');
        $dataForCategory = $this->getDataForCategoryWithFiltersMatchingSeoMix();

        $this->assertSame($dataForCategorySeoMix, $dataForCategory);
    }

    public function testReadyCategorySeoMixDataAreReturnedWhenOrderingIsNull(): void
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoPcNewWithUsb */
        $readyCategorySeoPcNewWithUsb = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_PC_NEW_WITH_USB, 1);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoPcNewWithUsb->getId()]);
        /** @var \App\Model\Category\Category $categoryPc */
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC);
        $categoryPcSlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);
        $data = $this->getDataForCategorySeoMixPcNewWithUsb(__DIR__ . '/../../_graphql/query/ReadyCategorySeoMixQuery.graphql', [
            'orderingMode' => null,
        ]);

        $this->assertSame($categoryPcSlug, $data['originalCategorySlug']);
        $this->assertSame($seoMixUrlSlug, $data['slug']);
    }

    public function testReadyCategorySeoMixDataAreReturnedWhenSameOrderingIsQueried(): void
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoPcNewWithUsb */
        $readyCategorySeoPcNewWithUsb = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_PC_NEW_WITH_USB, 1);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoPcNewWithUsb->getId()]);
        /** @var \App\Model\Category\Category $categoryPc */
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC);
        $categoryPcSlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);
        $data = $this->getDataForCategorySeoMixPcNewWithUsb(__DIR__ . '/../../_graphql/query/ReadyCategorySeoMixQuery.graphql', [
            'orderingMode' => strtoupper($readyCategorySeoPcNewWithUsb->getOrdering()),
        ]);

        $this->assertSame($categoryPcSlug, $data['originalCategorySlug']);
        $this->assertSame($seoMixUrlSlug, $data['slug']);
    }

    public function testCategoryDataAreReturnedWhenSeoCategoryWithOrderingIsQueried(): void
    {
        /** @var \App\Model\Category\Category $categoryPc */
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC);
        $categoryPcSlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);
        $data = $this->getDataForCategorySeoMixPcNewWithUsb(__DIR__ . '/../../_graphql/query/ReadyCategorySeoMixQuery.graphql', [
            'orderingMode' => 'NAME_ASC',
        ]);

        $this->assertNull($data['originalCategorySlug']);
        $this->assertSame($categoryPcSlug, $data['slug']);
    }

    public function testCategoryDataAreReturnedWhenSeoCategoryWithFilterIsQueried(): void
    {
        /** @var \App\Model\Product\Flag\Flag $flagSale */
        $flagSale = $this->getReference(FlagDataFixture::FLAG_PRODUCT_SALE);
        /** @var \App\Model\Category\Category $categoryPc */
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC);
        $categoryPcSlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);
        $data = $this->getDataForCategorySeoMixPcNewWithUsb(__DIR__ . '/../../_graphql/query/ReadyCategorySeoMixQuery.graphql', [
            'filter' => ['flags' => [$flagSale->getUuid()]],
        ]);

        $this->assertNull($data['originalCategorySlug']);
        $this->assertSame($categoryPcSlug, $data['slug']);
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
        $usbParameter = $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale));
        $yesValue = t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);

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
     * @param string $graphQlFilePath
     * @param array $additionalVariables
     * @return array
     */
    private function getDataForCategorySeoMixPcNewWithUsb(
        string $graphQlFilePath,
        array $additionalVariables = [],
    ): array {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoPcNewWithUsb */
        $readyCategorySeoPcNewWithUsb = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_PC_NEW_WITH_USB, 1);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoPcNewWithUsb->getId()]);
        $variables = array_merge($additionalVariables, ['slug' => $seoMixUrlSlug]);
        $responseForSeoMix = $this->getResponseContentForGql($graphQlFilePath, $variables);

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
        $parameterUsb = $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale));
        $categorySlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);
        $parameterValueYes = $this->parameterFacade->getParameterValueByValueTextAndLocale(
            t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            $firstDomainLocale,
        );
        $responseForCategory = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/SlugQueryCategoryMatchingSeoMix.graphql', [
            'slug' => $categorySlug,
            'orderingMode' => 'PRICE_DESC',
            'filter' => [
                'flags' => [$flagNew->getUuid()],
                'parameters' => [
                    [
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

    public function testCategoryFilterIsCorrectlySetAsSelected(): void
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $blackElectronicsSeoCategory */
        $blackElectronicsSeoCategory = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_BLACK_ELECTRONICS, 1);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $blackElectronicsSeoCategory->getId()]);
        $variables = array_merge(['slug' => $seoMixUrlSlug]);
        $responseForSeoMix = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/ReadyCategorySeoMixQuery.graphql', $variables);

        $data = $this->getResponseDataForGraphQlType($responseForSeoMix, 'slug');

        $colorFilter = array_filter(
            $data['products']['productFilterOptions']['parameters'],
            static function ($item) {
                return $item['__typename'] === 'ParameterColorFilterOption';
            },
        );

        $colorFilterValues = reset($colorFilter)['values'];
        $black = t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());

        $blackColorFilter = array_filter(
            $colorFilterValues,
            static function ($item) use ($black) {
                return $item['text'] === $black;
            },
        );

        $this->assertTrue(reset($blackColorFilter)['isSelected']);
    }
}
