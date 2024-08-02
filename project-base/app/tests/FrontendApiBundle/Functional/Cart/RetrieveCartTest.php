<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RetrieveCartTest extends GraphQlTestCase
{
    private Product $testingProduct;

    /**
     * @inject
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    private ProductAvailabilityFacade $productAvailabilityFacade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
    }

    public function testAddToCartResultIsValidForMoreQuantityThanOnStock(): void
    {
        $maximumAvailableQuantity = $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($this->testingProduct, $this->domain->getId());

        $desiredQuantity = $maximumAvailableQuantity + 3000;
        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . $desiredQuantity . '
                }
            ) {
                cart {
                    uuid
                    totalPrice{
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    }
                }
                addProductResult{
                    notOnStockQuantity
                    isNew
                    addedQuantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => 3000,
            'isNew' => true,
            'addedQuantity' => $maximumAvailableQuantity,
        ];

        self::assertEquals($expectedAddProductResultData, $newlyCreatedCart['addProductResult']);

        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId(), Vat::class);
        self::assertEquals($this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh, $maximumAvailableQuantity), $newlyCreatedCart['cart']['totalPrice']);
    }

    public function testAddToCartResultIsValidForMoreQuantityThanOnStockOnSecondAdd(): void
    {
        $maximumAvailableQuantity = $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($this->testingProduct, $this->domain->getId());

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
                cart {
                    uuid
                }
                addProductResult{
                    notOnStockQuantity
                    isNew
                    addedQuantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => 0,
            'isNew' => true,
            'addedQuantity' => $firstAddQuantity,
        ];

        self::assertEquals($expectedAddProductResultData, $newlyCreatedCart['addProductResult']);

        // add more of the same product into existing cart
        $mutation = 'mutation {
            AddToCart(
                input: {
                    cartUuid: "' . $newlyCreatedCart['cart']['uuid'] . '"
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . ($decrease + $notOnStockCount) . '
                }
            ) {
                cart {
                    uuid
                    items {
                        quantity
                    }
                }
                addProductResult {
                    notOnStockQuantity
                    isNew
                    addedQuantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $existingCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => $notOnStockCount,
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
                cart {
                    uuid
                }
                addProductResult{
                    notOnStockQuantity
                    isNew
                    addedQuantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => 0,
            'isNew' => true,
            'addedQuantity' => $desiredQuantity,
        ];

        self::assertEquals($expectedAddProductResultData, $newlyCreatedCart['addProductResult']);

        // add more of the same product into existing cart
        $mutation = 'mutation {
            AddToCart(
                input: {
                    cartUuid: "' . $newlyCreatedCart['cart']['uuid'] . '"
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: ' . $desiredQuantity . '
                }
            ) {
                cart {
                    uuid
                    items {
                        quantity
                    }
                }
                addProductResult {
                    notOnStockQuantity
                    isNew
                    addedQuantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $existingCart = $response['data']['AddToCart'];

        $expectedAddProductResultData = [
            'notOnStockQuantity' => 0,
            'isNew' => false,
            'addedQuantity' => $desiredQuantity,
        ];

        self::assertEquals($expectedAddProductResultData, $existingCart['addProductResult']);
        self::assertEquals($desiredQuantity * 2, $existingCart['cart']['items'][0]['quantity']);
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
                cart {
                    uuid
                    items {
                        uuid
                        product {
                            ' . $this->getAllProductAttributes() . '
                        }
                        quantity
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart']['cart'];

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}) {
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
                cart {    
                    uuid
                    items {
                        product {
                            ' . $this->getAllProductAttributes() . '
                        }
                        quantity
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $data = $response['data']['AddToCart']['cart'];

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
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $firstDomainLocale,
        );

        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId(), Vat::class);

        $fullName = sprintf(
            '%s %s %s',
            t('Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('plasma', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $mainCategory = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $subCategory = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);

        return [
            'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'slug' => '/' . $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 1], UrlGeneratorInterface::RELATIVE_PATH),
            'shortDescription' => $shortDescription,
            'seoH1' => t(
                'Hello Kitty Television',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'seoTitle' => t(
                'Hello Kitty TV',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'seoMetaDescription' => t(
                'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 1]),
            'unit' => [
                'name' => t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ],
            'availability' => [
                'name' => t('In stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'status' => AvailabilityStatusEnum::IN_STOCK,
            ],
            'stockQuantity' => 2700,
            'categories' => [
                [
                    'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
            ],
            'flags' => [
                [
                    'name' => t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'rgbColor' => '#ffffff',
                ],
            ],
            'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh),
            'brand' => [
                'name' => 'Sencor',
            ],
            'accessories' => [
                [
                    'name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('47" LG 47LA790V (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Apple iPhone 5S 64GB, gold', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Canon EH-22L', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Canon EOS 700D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('CD-R VERBATIM 210MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t(
                        'Kabel HDMI A - HDMI A M/M 2m gold-plated connectors High Speed HD',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale,
                    ),
                ],
                [
                    'name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
                [
                    'name' => t('24" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                ],
            ],
            'isSellingDenied' => false,
            'description' => t(
                'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback (DivX, XviD, MP3, WMA, JPEG), HDMI, SCART, VGA, pink execution, energ. Class B',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            'orderingPriority' => 1,
            'parameters' => [
                [
                    'name' => t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Main information', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => null,
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Connection method', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => null,
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Main information', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Main information', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => [
                        'name' => t('in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                    'values' => [
                        [
                            'text' => t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Connection method', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
            ],
            'namePrefix' => t('Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'nameSuffix' => t('plasma', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'fullName' => $fullName,
            'catalogNumber' => '9177759',
            'partNumber' => 'SLE 22F46DM4',
            'ean' => '8845781245930',
            'usps' => [
                t(
                    'Hello Kitty approved',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Immersive Full HD resolution',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Energy-Efficient Design',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Wide Color Gamut',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Adaptive Sync Technology',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
            ],
            'storeAvailabilities' => [
                [
                    'store' => [
                        'name' => 'Ostrava',
                    ],
                    'availabilityInformation' => t('Available immediately', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ], [
                    'store' => [
                        'name' => 'Pardubice',
                    ],
                    'availabilityInformation' => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => 1], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                    'availabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
                ],
            ],
            'availableStoresCount' => 1,
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
            namePrefix
            nameSuffix
            fullName
            catalogNumber
            partNumber
            ean
            usps
            storeAvailabilities {
                store {
                    name
                }
                availabilityInformation
                availabilityStatus
            }
            availableStoresCount
            breadcrumb {
                name
                slug
            }
        ';
    }
}
