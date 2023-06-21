<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Availability\AvailabilityStatusEnum;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
    }

    public function testAddToCartResultIsValidForMoreQuantityThanOnStock(): void
    {
        $maximumAvailableQuantity = $this->productAvailabilityFacade->getMaximumOrderQuantity($this->testingProduct, $this->domain->getId());

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

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId());
        self::assertEquals($this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh, $maximumAvailableQuantity), $newlyCreatedCart['cart']['totalPrice']);
    }

    public function testAddToCartResultIsValidForMoreQuantityThanOnStockOnSecondAdd(): void
    {
        $maximumAvailableQuantity = $this->productAvailabilityFacade->getMaximumOrderQuantity($this->testingProduct, $this->domain->getId());

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

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId());

        $fullName = sprintf(
            '%s %s %s',
            t('Televize', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('plazmová', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        /** @var \App\Model\Category\Category $mainCategory */
        $mainCategory = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        /** @var \App\Model\Category\Category $subCategory */
        $subCategory = $this->getReference(CategoryDataFixture::CATEGORY_TV);

        return [
            'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'slug' => '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova',
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
                'status' => AvailabilityStatusEnum::InStock->name,
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
            'orderingPriority' => 0,
            'parameters' => [
                [
                    'name' => t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Hlavní údaje', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
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
                    'name' => t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Hlavní údaje', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Hlavní údaje', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Způsob připojení', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
                [
                    'name' => t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => t('Způsob připojení', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
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
                    'name' => t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'group' => null,
                    'unit' => null,
                    'values' => [
                        [
                            'text' => t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
            ],
            'isUsingStock' => true,
            'namePrefix' => t('Televize', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'nameSuffix' => t('plazmová', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'fullName' => $fullName,
            'catalogNumber' => '9177759',
            'partNumber' => 'SLE 22F46DM4',
            'ean' => '8845781245930',
            'usps' => [
                t(
                    'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
                t(
                    'Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $firstDomainLocale,
                ),
            ],
            'hasPreorder' => false,
            'files' => [],
            'storeAvailabilities' => [
                [
                    'store' => [
                        'name' => 'Ostrava',
                    ],
                    'exposed' => true,
                    'availabilityInformation' => 'Ihned k odběru',
                    'availabilityStatus' => AvailabilityStatusEnum::InStock->name,
                ], [
                    'store' => [
                        'name' => 'Pardubice',
                    ],
                    'exposed' => false,
                    'availabilityInformation' => 'K dispozici za týden',
                    'availabilityStatus' => AvailabilityStatusEnum::InStock->name,
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
            files {
                anchorText
                url
            }
            storeAvailabilities {
                store {
                    name
                }
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
