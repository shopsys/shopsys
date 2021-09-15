<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Product;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RetrieveCartTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private Product $testingProduct;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
    }

    public function testAddToCartResultIsValidForMoreQuantityThanOnStock(): void
    {
        $productAvailabilityFacade = $this->getContainer()->get(ProductAvailabilityFacade::class);
        $maximumAvailableQuantity = $productAvailabilityFacade->getMaximumOrderQuantity($this->testingProduct, $this->domain->getId());

        $desiredQuantity = $maximumAvailableQuantity + 3000;
        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . $desiredQuantity . '
                }
            ) {
                uuid
                addProductResult{
                    notOnStockQuantity
                    overLimitQuantity
                    isQuantityOverLimit
                    isNew
                    addedQuantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => 3000,
            'overLimitQuantity' => null,
            'isQuantityOverLimit' => false,
            'isNew' => true,
            'addedQuantity' => $maximumAvailableQuantity,
        ];

        self::assertEquals($expectedAddProductResultData, $newlyCreatedCart['addProductResult']);
    }

    public function testAddToCartResultIsValidForMoreQuantityThanOnStockOnSecondAdd(): void
    {
        $productAvailabilityFacade = $this->getContainer()->get(ProductAvailabilityFacade::class);
        $maximumAvailableQuantity = $productAvailabilityFacade->getMaximumOrderQuantity($this->testingProduct, $this->domain->getId());

        $decrease = 200;
        $notOnStockCount = 3000;
        $firstAddQuantity = $maximumAvailableQuantity - $decrease;

        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . $firstAddQuantity . '
                }
            ) {
                uuid
                addProductResult{
                    notOnStockQuantity
                    overLimitQuantity
                    isQuantityOverLimit
                    isNew
                    addedQuantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => 0,
            'overLimitQuantity' => null,
            'isQuantityOverLimit' => false,
            'isNew' => true,
            'addedQuantity' => $firstAddQuantity,
        ];

        self::assertEquals($expectedAddProductResultData, $newlyCreatedCart['addProductResult']);

        // add more of the same product into existing cart
        $mutation = 'mutation {
            AddToCart(
                input: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . ($decrease + $notOnStockCount) . '
                }
            ) {
                uuid
                addProductResult {
                    notOnStockQuantity
                    overLimitQuantity
                    isQuantityOverLimit
                    isNew
                    addedQuantity
                }
                items {
                    quantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $existingCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => $notOnStockCount,
            'overLimitQuantity' => null,
            'isQuantityOverLimit' => false,
            'isNew' => false,
            'addedQuantity' => $decrease,
        ];

        self::assertEquals($maximumAvailableQuantity, $decrease + $firstAddQuantity);
        self::assertEquals($expectedAddProductResultData, $existingCart['addProductResult']);
    }

    public function testAddToCartResultIsValidForQuantityOnStock(): void
    {
        $desiredQuantity = 6;
        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . $desiredQuantity . '
                }
            ) {
                uuid
                addProductResult{
                    notOnStockQuantity
                    overLimitQuantity
                    isQuantityOverLimit
                    isNew
                    addedQuantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => 0,
            'overLimitQuantity' => null,
            'isQuantityOverLimit' => false,
            'isNew' => true,
            'addedQuantity' => $desiredQuantity,
        ];

        self::assertEquals($expectedAddProductResultData, $newlyCreatedCart['addProductResult']);

        // add more of the same product into existing cart
        $mutation = 'mutation {
            AddToCart(
                input: {
                    cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . $desiredQuantity . '
                }
            ) {
                uuid
                addProductResult {
                    notOnStockQuantity
                    overLimitQuantity
                    isQuantityOverLimit
                    isNew
                    addedQuantity
                }
                items {
                    quantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $existingCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => 0,
            'overLimitQuantity' => null,
            'isQuantityOverLimit' => false,
            'isNew' => false,
            'addedQuantity' => $desiredQuantity,
        ];

        self::assertEquals($expectedAddProductResultData, $existingCart['addProductResult']);
        self::assertEquals($desiredQuantity * 2, $existingCart['items'][0]['quantity']);
    }

    public function testProductFromCartCanBeRetrieved(): void
    {
        $desiredQuantity = 6;
        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . $desiredQuantity . '
                }
            ) {
                uuid
                items {
                    uuid
                    product {
                        ' . $this->getAllProductAttributes() . '
                    }
                    quantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        $getCartQuery = '{
            cart(uuid: "' . $newlyCreatedCart['uuid'] . '") {
                items {
                    product {
                        ' . $this->getAllProductAttributes() . '
                    }
                    quantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $data = $response['data']['cart'];

        self::assertEquals($this->getExpectedProductDetailWithAllAttributes(), $data['items'][0]['product']);
        self::assertEquals($desiredQuantity, $data['items'][0]['quantity']);
    }

    public function testAddProductToCartReturnsProduct(): void
    {
        $desiredQuantity = 6;
        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . $desiredQuantity . '
                }
            ) {
                uuid
                items {
                    product {
                        ' . $this->getAllProductAttributes() . '
                    }
                    quantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $data = $response['data']['AddToCart'];

        self::assertEquals($this->getExpectedProductDetailWithAllAttributes(), $data['items'][0]['product']);
        self::assertEquals($desiredQuantity, $data['items'][0]['quantity']);
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
            'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale),
            'slug' => '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova',
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
                    'slug' => $this->urlGenerator->generate('front_product_detail', ['id' => $this->testingProduct->getId()]),
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    private function getAllProductAttributes(): string
    {
        return '
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
            }
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
        ';
    }
}
