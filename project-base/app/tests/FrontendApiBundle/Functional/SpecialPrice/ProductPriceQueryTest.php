<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\SpecialPrice;

use App\DataFixtures\Demo\ProductDataFixture;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Money\HiddenMoney;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrontendApiBundle\Model\Resolver\Price\ProductPriceQuery;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ProductPriceQueryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private readonly ProductPriceQuery $productPriceQuery;

    public function testProductUponInquiryReturnsHiddenPrice(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3', Product::class);
        $priceInfo = $this->productPriceQuery->priceByProductQuery($product);

        $this->assertSame($product->getProductType(), ProductTypeEnum::TYPE_INQUIRY, 'Product should be upon inquiry');

        $this->assertInstanceOf(HiddenMoney::class, $priceInfo->priceWithoutVat, 'Price without VAT should be hidden for product upon inquiry');
        $this->assertInstanceOf(HiddenMoney::class, $priceInfo->priceWithVat, 'Price with VAT should be hidden for product upon inquiry');
        $this->assertInstanceOf(HiddenMoney::class, $priceInfo->vatAmount, 'VAT amount should be hidden for product upon inquiry');
        $this->assertInstanceOf(HiddenMoney::class, $priceInfo->basicPrice->getPriceWithoutVat(), 'Basic price without VAT should be hidden for product upon inquiry');
        $this->assertInstanceOf(HiddenMoney::class, $priceInfo->basicPrice->getPriceWithVat(), 'Basic price with VAT should be hidden for product upon inquiry');
        $this->assertInstanceOf(HiddenMoney::class, $priceInfo->basicPrice->getVatAmount(), 'Basic price VAT amount should be hidden for product upon inquiry');
    }

    public function testPriceIsReturnedWhenNoSpecialPricePresent(): void
    {
        $data = [
            'product_type' => ProductTypeEnum::TYPE_BASIC,
            'prices' => [
                [
                    'price_without_vat' => 115.67,
                    'price_with_vat' => 139.96,
                    'vat' => 24.29,
                    'price_from' => false,
                    'pricing_group_id' => 1,
                ],
            ],
            'special_prices' => [],
        ];

        $priceInfo = $this->productPriceQuery->priceByProductQuery($data);

        $this->assertMoney('115.67', $priceInfo->priceWithoutVat);
        $this->assertMoney('139.96', $priceInfo->priceWithVat);
        $this->assertMoney('24.29', $priceInfo->vatAmount);
        $this->assertFalse($priceInfo->isPriceFrom);
        $this->assertMoney('115.67', $priceInfo->basicPrice->getPriceWithoutVat());
        $this->assertMoney('139.96', $priceInfo->basicPrice->getPriceWithVat());
        $this->assertMoney('24.29', $priceInfo->basicPrice->getVatAmount());
        $this->assertNull($priceInfo->percentageDiscount);
        $this->assertNull($priceInfo->nextPriceChange);
    }

    public function testSpecialPriceIsReturnedWhenValid(): void
    {
        $data = [
            'product_type' => ProductTypeEnum::TYPE_BASIC,
            'prices' => [
                [
                    'price_without_vat' => 115.67,
                    'price_with_vat' => 139.96,
                    'vat' => 24.29,
                    'price_from' => false,
                    'pricing_group_id' => 1,
                ],
            ],
            'special_prices' => [
                [
                    'price_list_id' => 1,
                    'price_list_name' => 'Special offers',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-01-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 99.17,
                            'price_with_vat' => 120.0,
                            'vat' => 20.83,
                            'product_id' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $priceInfo = $this->productPriceQuery->priceByProductQuery($data);

        $this->assertMoney('99.17', $priceInfo->priceWithoutVat);
        $this->assertMoney('120', $priceInfo->priceWithVat);
        $this->assertMoney('20.83', $priceInfo->vatAmount);
        $this->assertFalse($priceInfo->isPriceFrom);
        $this->assertMoney('115.67', $priceInfo->basicPrice->getPriceWithoutVat());
        $this->assertMoney('139.96', $priceInfo->basicPrice->getPriceWithVat());
        $this->assertMoney('24.29', $priceInfo->basicPrice->getVatAmount());
        $this->assertSame(14.0, $priceInfo->percentageDiscount);
        $this->assertEquals(new DateTimeImmutable('2084-01-10 08:30:00'), $priceInfo->nextPriceChange);
    }

    public function testProperSpecialPriceIsReturnedWhenMultiple(): void
    {
        $data = [
            'product_type' => ProductTypeEnum::TYPE_BASIC,
            'prices' => [
                [
                    'price_without_vat' => 115.67,
                    'price_with_vat' => 139.96,
                    'vat' => 24.29,
                    'price_from' => false,
                    'pricing_group_id' => 1,
                ],
            ],
            'special_prices' => [
                [
                    'price_list_id' => 1,
                    'price_list_name' => 'Special offers',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-01-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 99.17,
                            'price_with_vat' => 120.0,
                            'vat' => 20.83,
                            'product_id' => 1,
                        ],
                    ],
                ],
                [
                    'price_list_id' => 2,
                    'price_list_name' => 'Items on sale',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-01-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 79.34,
                            'price_with_vat' => 96.0,
                            'vat' => 16.66,
                            'product_id' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $priceInfo = $this->productPriceQuery->priceByProductQuery($data);

        $this->assertMoney('99.17', $priceInfo->priceWithoutVat);
        $this->assertMoney('120', $priceInfo->priceWithVat);
        $this->assertMoney('20.83', $priceInfo->vatAmount);
        $this->assertFalse($priceInfo->isPriceFrom);
        $this->assertMoney('115.67', $priceInfo->basicPrice->getPriceWithoutVat());
        $this->assertMoney('139.96', $priceInfo->basicPrice->getPriceWithVat());
        $this->assertMoney('24.29', $priceInfo->basicPrice->getVatAmount());
        $this->assertSame(14.0, $priceInfo->percentageDiscount);
        $this->assertEquals(new DateTimeImmutable('2084-01-10 08:30:00'), $priceInfo->nextPriceChange);
    }

    public function testOutdatedSpecialPriceIsSkipped(): void
    {
        $data = [
            'product_type' => ProductTypeEnum::TYPE_BASIC,
            'prices' => [
                [
                    'price_without_vat' => 115.67,
                    'price_with_vat' => 139.96,
                    'vat' => 24.29,
                    'price_from' => false,
                    'pricing_group_id' => 1,
                ],
            ],
            'special_prices' => [
                [
                    'price_list_id' => 2,
                    'price_list_name' => 'Items on sale',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2023-02-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 79.34,
                            'price_with_vat' => 96.0,
                            'vat' => 16.66,
                            'product_id' => 1,
                        ],
                    ],
                ],
                [
                    'price_list_id' => 1,
                    'price_list_name' => 'Special offers',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-01-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 99.17,
                            'price_with_vat' => 120.0,
                            'vat' => 20.83,
                            'product_id' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $priceInfo = $this->productPriceQuery->priceByProductQuery($data);

        $this->assertMoney('99.17', $priceInfo->priceWithoutVat);
        $this->assertMoney('120', $priceInfo->priceWithVat);
        $this->assertMoney('20.83', $priceInfo->vatAmount);
        $this->assertFalse($priceInfo->isPriceFrom);
        $this->assertMoney('115.67', $priceInfo->basicPrice->getPriceWithoutVat());
        $this->assertMoney('139.96', $priceInfo->basicPrice->getPriceWithVat());
        $this->assertMoney('24.29', $priceInfo->basicPrice->getVatAmount());
        $this->assertSame(14.0, $priceInfo->percentageDiscount);
        $this->assertEquals(new DateTimeImmutable('2084-01-10 08:30:00'), $priceInfo->nextPriceChange);
    }

    public function testHigherSpecialPriceIsSkipped(): void
    {
        $data = [
            'product_type' => ProductTypeEnum::TYPE_BASIC,
            'prices' => [
                [
                    'price_without_vat' => 115.67,
                    'price_with_vat' => 139.96,
                    'vat' => 24.29,
                    'price_from' => false,
                    'pricing_group_id' => 1,
                ],
            ],
            'special_prices' => [
                [
                    'price_list_id' => 1,
                    'price_list_name' => 'Special offers',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-01-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 132.23,
                            'price_with_vat' => 160.0,
                            'vat' => 7.77,
                            'product_id' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $priceInfo = $this->productPriceQuery->priceByProductQuery($data);

        $this->assertMoney('115.67', $priceInfo->priceWithoutVat);
        $this->assertMoney('139.96', $priceInfo->priceWithVat);
        $this->assertMoney('24.29', $priceInfo->vatAmount);
        $this->assertFalse($priceInfo->isPriceFrom);
        $this->assertMoney('115.67', $priceInfo->basicPrice->getPriceWithoutVat());
        $this->assertMoney('139.96', $priceInfo->basicPrice->getPriceWithVat());
        $this->assertMoney('24.29', $priceInfo->basicPrice->getVatAmount());
        $this->assertNull($priceInfo->percentageDiscount);
        $this->assertNull($priceInfo->nextPriceChange);
    }

    public function testFutureSpecialPriceOnlyReturnsNextPriceChange(): void
    {
        $data = [
            'product_type' => ProductTypeEnum::TYPE_BASIC,
            'prices' => [
                [
                    'price_without_vat' => 115.67,
                    'price_with_vat' => 139.96,
                    'vat' => 24.29,
                    'price_from' => false,
                    'pricing_group_id' => 1,
                ],
            ],
            'special_prices' => [
                [
                    'price_list_id' => 1,
                    'price_list_name' => 'Special offers',
                    'valid_from' => '2084-01-10 08:30:00',
                    'valid_to' => '2084-02-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 99.17,
                            'price_with_vat' => 120.0,
                            'vat' => 20.83,
                            'product_id' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $priceInfo = $this->productPriceQuery->priceByProductQuery($data);

        $this->assertMoney('115.67', $priceInfo->priceWithoutVat);
        $this->assertMoney('139.96', $priceInfo->priceWithVat);
        $this->assertMoney('24.29', $priceInfo->vatAmount);
        $this->assertFalse($priceInfo->isPriceFrom);
        $this->assertMoney('115.67', $priceInfo->basicPrice->getPriceWithoutVat());
        $this->assertMoney('139.96', $priceInfo->basicPrice->getPriceWithVat());
        $this->assertMoney('24.29', $priceInfo->basicPrice->getVatAmount());
        $this->assertNull($priceInfo->percentageDiscount);
        $this->assertEquals(new DateTimeImmutable('2084-01-10 08:30:00'), $priceInfo->nextPriceChange);
    }

    /**
     * @param array $specialPrices
     * @param string $expectedPriceWithVat
     * @param string $expectedPriceWithoutVat
     * @param string $expectedVatAmount
     * @param float $expectedPercentageDiscount
     * @param string $expectedNextPriceChange
     */
    #[DataProvider('getVariantSpecialPrices')]
    public function testValidSpecialPriceIsReturnedForVariants(
        array $specialPrices,
        string $expectedPriceWithVat,
        string $expectedPriceWithoutVat,
        string $expectedVatAmount,
        float $expectedPercentageDiscount,
        string $expectedNextPriceChange,
    ): void {
        $data = [
            'product_type' => ProductTypeEnum::TYPE_BASIC,
            'prices' => [
                [
                    'price_without_vat' => 115.67,
                    'price_with_vat' => 139.96,
                    'vat' => 24.29,
                    'price_from' => true,
                    'pricing_group_id' => 1,
                ],
            ],
            'special_prices' => $specialPrices,
        ];

        $priceInfo = $this->productPriceQuery->priceByProductQuery($data);
        $this->assertMoney($expectedPriceWithoutVat, $priceInfo->priceWithoutVat);
        $this->assertMoney($expectedPriceWithVat, $priceInfo->priceWithVat);
        $this->assertMoney($expectedVatAmount, $priceInfo->vatAmount);
        $this->assertSame($expectedPercentageDiscount, $priceInfo->percentageDiscount);
        $this->assertEquals(new DateTimeImmutable($expectedNextPriceChange), $priceInfo->nextPriceChange);
        $this->assertTrue($priceInfo->isPriceFrom);
        $this->assertMoney('115.67', $priceInfo->basicPrice->getPriceWithoutVat());
        $this->assertMoney('139.96', $priceInfo->basicPrice->getPriceWithVat());
        $this->assertMoney('24.29', $priceInfo->basicPrice->getVatAmount());
    }

    /**
     * @return iterable
     */
    public static function getVariantSpecialPrices(): iterable
    {
        yield 'lowest price should be selected' => [
            'specialPrices' => [
                [
                    'price_list_id' => 1,
                    'price_list_name' => 'Special offers',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-02-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 99.17,
                            'price_with_vat' => 120.0,
                            'vat' => 20.83,
                            'product_id' => 1,
                        ],
                        [
                            'price_without_vat' => 82.65,
                            'price_with_vat' => 100,
                            'vat' => 17.35,
                            'product_id' => 2,
                        ],
                    ],
                ],
            ],
            'expectedPriceWithVat' => '100',
            'expectedPriceWithoutVat' => '82.65',
            'expectedVatAmount' => '17.35',
            'expectedPercentageDiscount' => 28.0,
            'expectedNextPriceChange' => '2084-02-10 08:30:00',
        ];

        yield 'another price list with the same product is ignored' => [
            'specialPrices' => [
                [
                    'price_list_id' => 1,
                    'price_list_name' => 'Special offers',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-02-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 99.17,
                            'price_with_vat' => 120.0,
                            'vat' => 20.83,
                            'product_id' => 1,
                        ],
                        [
                            'price_without_vat' => 82.65,
                            'price_with_vat' => 100,
                            'vat' => 17.35,
                            'product_id' => 2,
                        ],
                    ],
                ],
                [
                    'price_list_id' => 2,
                    'price_list_name' => 'Items on sale',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-02-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 74, 38,
                            'price_with_vat' => 90.0,
                            'vat' => 15.62,
                            'product_id' => 1,
                        ],
                    ],
                ],
            ],
            'expectedPriceWithVat' => '100',
            'expectedPriceWithoutVat' => '82.65',
            'expectedVatAmount' => '17.35',
            'expectedPercentageDiscount' => 28.0,
            'expectedNextPriceChange' => '2084-02-10 08:30:00',
        ];

        yield 'lowest price is selected across price lists' => [
            'specialPrices' => [
                [
                    'price_list_id' => 1,
                    'price_list_name' => 'Special offers',
                    'valid_from' => '2023-02-09 08:30:00',
                    'valid_to' => '2084-02-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 99.17,
                            'price_with_vat' => 120.0,
                            'vat' => 20.83,
                            'product_id' => 1,
                        ],
                        [
                            'price_without_vat' => 82.65,
                            'price_with_vat' => 100,
                            'vat' => 17.35,
                            'product_id' => 2,
                        ],
                    ],
                ],
                [
                    'price_list_id' => 2,
                    'price_list_name' => 'Items on sale',
                    'valid_from' => '2023-01-10 08:30:00',
                    'valid_to' => '2084-01-10 08:30:00',
                    'prices' => [
                        [
                            'price_without_vat' => 74.38,
                            'price_with_vat' => 90.0,
                            'vat' => 15.62,
                            'product_id' => 3,
                        ],
                    ],
                ],
            ],
            'expectedPriceWithVat' => '90.0',
            'expectedPriceWithoutVat' => '74.38',
            'expectedVatAmount' => '15.62',
            'expectedPercentageDiscount' => 35,
            'expectedNextPriceChange' => '2084-01-10 08:30:00',
        ];
    }
}
