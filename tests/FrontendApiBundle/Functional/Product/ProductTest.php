<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private $product;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        $productFacade = $this->getContainer()->get(ProductFacade::class);

        /** @var \App\Model\Product\Product $product */
        $product = $productFacade->getById(1);
        $this->product = $product;

        parent::setUp();
    }

    public function testProductDetailNameByUuid(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->product->getUuid() . '") {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'product' => [
                    'name' => t(
                        '22" Sencor SLE 22F46DM4 HELLO KITTY',
                        [],
                        'dataFixtures',
                        $this->getLocaleForFirstDomain()
                    ),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testProductDetailWithAllAttributesByUuid(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->product->getUuid() . '") {
                    name
                    slug
                    shortDescription
                    seoH1
                    seoTitle
                    seoMetaDescription
                    link
                    unit {
                        name
                    }
                    availability {
                        name
                        status
                    }
                    stockQuantity
                    categories {
                        name
                    }
                    flags {
                        name
                        rgbColor
                    }
                    price {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    },
                    brand {
                        name
                    }
                    accessories {
                        name
                    }
                    isSellingDenied
                    description
                    orderingPriority
                    parameters {
                        name
                        group
                        unit {
                            name
                        }
                        values {
                            text
                        }
                    }
                    isUsingStock
                    namePrefix
                    nameSuffix
                    fullName
                    catalogNumber
                    partNumber
                    ean
                    usps
                    isInSale
                    hasPreorder
                    hasSaleExclusion
                    files {
                      anchorText
                      url
                    }
                    storeAvailabilities {
                        storeName
                        exposed
                        availabilityInformation
                        availabilityStatus
                    }
                    availableStoresCount
                    exposedStoresCount
                    breadcrumb {
                        name
                        slug
                    }
                }
            }
        ';

        $this->assertQueryWithExpectedArray($query, $this->getExpectedProductDetailWithAllAttributes());
    }

    /**
     * @return array
     */
    private function getExpectedProductDetailWithAllAttributes(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $shortDescription = t(
            'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback',
            [],
            'dataFixtures',
            $firstDomainLocale
        );

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId());

        $fullName = sprintf(
            '%s %s %s',
            t('Televize', [], 'dataFixtures', $firstDomainLocale),
            t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale),
            t('plazmová', [], 'dataFixtures', $firstDomainLocale),
        );

        /** @var \App\Model\Category\Category $mainCategory */
        $mainCategory = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        /** @var \App\Model\Category\Category $subCategory */
        $subCategory = $this->getReference(CategoryDataFixture::CATEGORY_TV);

        return [
            'data' => [
                'product' => [
                    'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale),
                    'slug' => 'televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova',
                    'shortDescription' => $shortDescription,
                    'seoH1' => t(
                        'Hello Kitty Television',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    ),
                    'seoTitle' => t(
                        'Hello Kitty TV',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    ),
                    'seoMetaDescription' => t(
                        'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    ),
                    'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 1]),
                    'unit' => [
                        'name' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'availability' => [
                        'name' => t('In stock', [], 'dataFixtures', $firstDomainLocale),
                        'status' => 'in-stock',
                    ],
                    'stockQuantity' => 2700,
                    'categories' => [
                        [
                            'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                        ],
                        [
                            'name' => t('TV, audio', [], 'dataFixtures', $firstDomainLocale),
                        ],
                    ],
                    'flags' => [
                        [
                            'name' => t('Action', [], 'dataFixtures', $firstDomainLocale),
                            'rgbColor' => '#ffffff',
                        ],
                    ],
                    'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
                    'brand' => [
                        'name' => 'Sencor',
                    ],
                    'accessories' => [
                        [
                            'name' => t(
                                'Kabel HDMI A - HDMI A M/M 2m gold-plated connectors High Speed HD',
                                [],
                                'dataFixtures',
                                $firstDomainLocale
                            ),
                        ],
                        [
                            'name' => t('Defender 2.0 SPK-480', [], 'dataFixtures', $firstDomainLocale),
                        ],
                    ],
                    'isSellingDenied' => false,
                    'description' => t(
                        'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback (DivX, XviD, MP3, WMA, JPEG), HDMI, SCART, VGA, pink execution, energ. Class B',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    ),
                    'orderingPriority' => 0,
                    'parameters' => [
                        [
                            'name' => t('Screen size', [], 'dataFixtures', $firstDomainLocale),
                            'group' => t('Hlavní údaje', [], 'dataFixtures', $firstDomainLocale),
                            'unit' => [
                                'name' => t('in', [], 'dataFixtures', $firstDomainLocale),
                            ],
                            'values' => [
                                [
                                    'text' => t('27"', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('Technology', [], 'dataFixtures', $firstDomainLocale),
                            'group' => t('Hlavní údaje', [], 'dataFixtures', $firstDomainLocale),
                            'unit' => null,
                            'values' => [
                                [
                                    'text' => t('LED', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('Resolution', [], 'dataFixtures', $firstDomainLocale),
                            'group' => t('Hlavní údaje', [], 'dataFixtures', $firstDomainLocale),
                            'unit' => null,
                            'values' => [
                                [
                                    'text' => t('1920×1080 (Full HD)', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('USB', [], 'dataFixtures', $firstDomainLocale),
                            'group' => t('Způsob připojení', [], 'dataFixtures', $firstDomainLocale),
                            'unit' => null,
                            'values' => [
                                [
                                    'text' => t('Yes', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('HDMI', [], 'dataFixtures', $firstDomainLocale),
                            'group' => t('Způsob připojení', [], 'dataFixtures', $firstDomainLocale),
                            'unit' => null,
                            'values' => [
                                [
                                    'text' => t('Yes', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('Barva', [], 'dataFixtures', $firstDomainLocale),
                            'group' => null,
                            'unit' => null,
                            'values' => [
                                [
                                    'text' => t('červená', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('Materiál', [], 'dataFixtures', $firstDomainLocale),
                            'group' => null,
                            'unit' => null,
                            'values' => [
                                [
                                    'text' => t('kov', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                    ],
                    'isUsingStock' => true,
                    'namePrefix' => t('Televize', [], 'dataFixtures', $firstDomainLocale),
                    'nameSuffix' => t('plazmová', [], 'dataFixtures', $firstDomainLocale),
                    'fullName' => $fullName,
                    'catalogNumber' => '9177759',
                    'partNumber' => 'SLE 22F46DM4',
                    'ean' => '8845781245930',
                    'usps' => [],
                    'isInSale' => false,
                    'hasPreorder' => false,
                    'hasSaleExclusion' => false,
                    'files' => [],
                    'storeAvailabilities' => [
                        [
                            'storeName' => 'Ostrava',
                            'exposed' => true,
                            'availabilityInformation' => 'Ihned k odběru',
                            'availabilityStatus' => 'in-stock',
                        ], [
                            'storeName' => 'Pardubice',
                            'exposed' => false,
                            'availabilityInformation' => 'K dispozici za týden',
                            'availabilityStatus' => 'in-stock',
                        ],
                    ],
                    'availableStoresCount' => 1,
                    'exposedStoresCount' => 1,
                    'breadcrumb' => [
                        [
                            'name' => $mainCategory->getName($firstDomainLocale),
                            'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $mainCategory->getId()]),
                        ],
                        [
                            'name' => $subCategory->getName($firstDomainLocale),
                            'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $subCategory->getId()]),
                        ],
                        [
                            'name' => $fullName,
                            'slug' => $this->urlGenerator->generate('front_product_detail', ['id' => $this->product->getId()]),
                        ],
                    ],
                ],
            ],
        ];
    }
}
