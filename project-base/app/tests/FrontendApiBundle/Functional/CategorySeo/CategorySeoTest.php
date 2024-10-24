<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\CategorySeo;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use App\DataFixtures\Demo\ReadyCategorySeoDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorter;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategorySeoTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    private ParameterFacade $parameterFacade;

    public function testGetReadyCategorySeoMixData(): void
    {
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_ELECTRONICS_WITHOUT_HDMI_PROMOTION, 1, ReadyCategorySeoMix::class);

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
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategorySeoWithLinks.graphql', [
            'urlSlug' => '/elektro-bez-hdmi-akce',
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertSame($arrayExpected, $data);
    }

    public function testReadyCategorySeoMixProductsOrdering(): void
    {
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_FROM_CHEAPEST, 1, ReadyCategorySeoMix::class);
        $urlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoMix->getId()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategorySeo.graphql', [
            'urlSlug' => $urlSlug,
            'first' => 1,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $arrayExpected = [
            'orderingMode' => 'PRICE_ASC',
            'defaultOrderingMode' => 'PRICE_ASC',
            'edges' => [
                ['node' => ['name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())]],
            ],
        ];

        $this->assertSame($arrayExpected, $data['products']);
    }

    public function testReadyCategorySeoMixProductsWithFlag(): void
    {
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_IN_SALE, 1, ReadyCategorySeoMix::class);
        $urlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoMix->getId()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategorySeo.graphql', [
            'urlSlug' => $urlSlug,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $arrayExpected = [
            'orderingMode' => 'PRIORITY',
            'defaultOrderingMode' => 'PRIORITY',
            'edges' => [
                ['node' => ['name' => t('Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())]],
            ],
        ];

        $this->assertSame($arrayExpected, $data['products']);
    }

    public function testReadyCategorySeoMixProductsWithParameters(): void
    {
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_PLASMA_WITH_HDMI, 1, ReadyCategorySeoMix::class);
        $urlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoMix->getId()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategorySeo.graphql', [
            'urlSlug' => $urlSlug,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');


        $arrayExpected = [
            'orderingMode' => 'PRIORITY',
            'defaultOrderingMode' => 'PRIORITY',
            'edges' => [
                ['node' => ['name' => t('32" Hyundai 32PFL4400', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())]],
            ],
        ];

        $this->assertSame($arrayExpected, $data['products']);
    }

    public function testReadyCategorySeoMixReturnsSelectedFilterOptions(): void
    {
        $data = $this->getDataForCategorySeoMixPcNewWithUsb();

        $this->assertSelectedFlags($data['products']['productFilterOptions']['flags']);
        $this->assertSelectedParameterCheckboxFilterOptions($data['products']['productFilterOptions']['parameters']);
    }

    public function testReadyCategorySeoMixDataAreReturnedWhenMatchedFromCategory(): void
    {
        $dataForCategorySeoMix = $this->getDataForCategorySeoMixPcNewWithUsb();
        $dataForCategory = $this->getDataForCategoryWithFiltersMatchingSeoMix();

        $this->assertSame($dataForCategorySeoMix, $dataForCategory);
    }

    public function testReadyCategorySeoMixDataAreReturnedWhenOrderingIsNull(): void
    {
        $readyCategorySeoPcNewWithUsb = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_PC_NEW_WITH_USB, 1, ReadyCategorySeoMix::class);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoPcNewWithUsb->getId()]);

        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class);
        $categoryPcSlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);

        $data = $this->getDataForCategorySeoMixPcNewWithUsb(['orderingMode' => null]);

        $this->assertSame($categoryPcSlug, $data['originalCategorySlug']);
        $this->assertSame($seoMixUrlSlug, $data['slug']);
    }

    public function testReadyCategorySeoMixDataAreReturnedWhenSameOrderingIsQueried(): void
    {
        $readyCategorySeoPcNewWithUsb = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_PC_NEW_WITH_USB, 1, ReadyCategorySeoMix::class);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoPcNewWithUsb->getId()]);

        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class);
        $categoryPcSlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);

        $data = $this->getDataForCategorySeoMixPcNewWithUsb(['orderingMode' => strtoupper($readyCategorySeoPcNewWithUsb->getOrdering())]);

        $this->assertSame($categoryPcSlug, $data['originalCategorySlug']);
        $this->assertSame($seoMixUrlSlug, $data['slug']);
    }

    public function testCategoryDataAreReturnedWhenSeoCategoryWithOrderingIsQueried(): void
    {
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class);
        $categoryPcSlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);

        $data = $this->getDataForCategorySeoMixPcNewWithUsb(['orderingMode' => 'NAME_ASC']);

        $this->assertNull($data['originalCategorySlug']);
        $this->assertSame($categoryPcSlug, $data['slug']);
    }

    public function testCategoryDataAreReturnedWhenSeoCategoryWithFilterIsQueried(): void
    {
        $flagSale = $this->getReference(FlagDataFixture::FLAG_PRODUCT_SALE, Flag::class);
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class);
        $categoryPcSlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);
        $data = $this->getDataForCategorySeoMixPcNewWithUsb([
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
        $newFlag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class);

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
        $usbParameter = $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class);
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
     * @param array $additionalVariables
     * @return array
     */
    private function getDataForCategorySeoMixPcNewWithUsb(
        array $additionalVariables = [],
    ): array {
        $readyCategorySeoPcNewWithUsb = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_PC_NEW_WITH_USB, 1, ReadyCategorySeoMix::class);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoPcNewWithUsb->getId()]);

        $variables = [...$additionalVariables, 'urlSlug' => $seoMixUrlSlug];

        $responseForSeoMix = $this->getResponseContentForGql(__DIR__ . '/graphql/CategorySeoWithFilters.graphql', $variables);

        return $this->getResponseDataForGraphQlType($responseForSeoMix, 'category');
    }

    /**
     * @return array
     */
    private function getDataForCategoryWithFiltersMatchingSeoMix(): array
    {
        $firstDomainLocale = $this->getFirstDomainLocale();

        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class);
        $flagNew = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class);
        $parameterUsb = $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class);
        $categorySlug = $this->urlGenerator->generate('front_product_list', ['id' => $categoryPc->getId()]);
        $parameterValueYes = $this->parameterFacade->getParameterValueByValueTextAndLocale(
            t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            $firstDomainLocale,
        );

        $responseForCategory = $this->getResponseContentForGql(__DIR__ . '/graphql/CategorySeoWithFilters.graphql', [
            'urlSlug' => $categorySlug,
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

        return $this->getResponseDataForGraphQlType($responseForCategory, 'category');
    }

    public function testCategoryFilterIsCorrectlySetAsSelected(): void
    {
        $blackElectronicsSeoCategory = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_BLACK_ELECTRONICS, 1, ReadyCategorySeoMix::class);
        $seoMixUrlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $blackElectronicsSeoCategory->getId()]);
        $responseForSeoMix = $this->getResponseContentForGql(__DIR__ . '/graphql/CategorySeoWithFilters.graphql', ['urlSlug' => $seoMixUrlSlug]);

        $data = $this->getResponseDataForGraphQlType($responseForSeoMix, 'category');

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
