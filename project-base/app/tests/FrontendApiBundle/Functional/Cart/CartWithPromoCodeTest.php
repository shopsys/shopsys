<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Tests\FrontendApiBundle\Functional\Order\OrderTestTrait;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartWithPromoCodeTest extends GraphQlTestCase
{
    use OrderTestTrait;

    public function testCartManipulationWithPromoCode(): void
    {
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $initCartResponse = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product1->getUuid(),
            'quantity' => 1,
        ]);

        $initCartResult = $initCartResponse['data']['AddToCart'];
        $cartUuid = $initCartResult['cart']['uuid'];

        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, Domain::FIRST_DOMAIN_ID, PromoCode::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $cartUuid,
            'promoCode' => $validPromoCode->getCode(),
        ]);
        $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        $query = 'query {
            cart (cartInput: {
                cartUuid: "' . $cartUuid . '"
            }){
                uuid
                items {
                    uuid
                    quantity
                    discounts {
                        promoCode
                        totalDiscount {
                            priceWithVat
                            priceWithoutVat
                            vatAmount
                        }
                        unitDiscount {
                            priceWithVat
                            priceWithoutVat
                            vatAmount
                        }
                    }
                }
                totalPrice {
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                }
                totalDiscountPrice {
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                }
            }
        }';

        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId(), Vat::class);

        $cartData = $this->getResponseContentForQuery($query)['data']['cart'];
        $this->assertSame($this->getSerializedPriceConvertedToDomainDefaultCurrency('2602.48', $vatHigh), $cartData['totalPrice']);
        $this->assertSame($this->getSerializedPriceConvertedToDomainDefaultCurrency('289.26', $vatHigh), $cartData['totalDiscountPrice']);

        $this->assertSame(
            [
                [
                    'promoCode' => 'test',
                    'totalDiscount' => [
                        'priceWithVat' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-350.000000'),
                        'priceWithoutVat' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-289.250000'),
                        'vatAmount' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-60.750000'),
                    ],
                    'unitDiscount' => [
                        'priceWithVat' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-350.000000'),
                        'priceWithoutVat' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-289.250000'),
                        'vatAmount' => $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('-60.750000'),
                    ],
                ],
            ],
            $cartData['items'][0]['discounts'],
        );


        $product72 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);

        $addAnotherToCartResponse = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product72->getUuid(),
            'quantity' => 1,
        ]);

        $totalPrice = self::getOrderTotalPriceByExpectedOrderItems([
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2602.48', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('90', $vatHigh)],
        ]);

        $totalPriceExpected = [
            'priceWithVat' => $totalPrice->getPriceWithVat()->getAmount(),
            'priceWithoutVat' => $totalPrice->getPriceWithoutVat()->getAmount(),
            'vatAmount' => $totalPrice->getVatAmount()->getAmount(),
        ];

        $totalDiscountPrice = self::getOrderTotalPriceByExpectedOrderItems([
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('289.26', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('9.92', $vatHigh)],
        ]);

        $totalDiscountPriceExpected = [
            'priceWithVat' => $totalDiscountPrice->getPriceWithVat()->getAmount(),
            'priceWithoutVat' => $totalDiscountPrice->getPriceWithoutVat()->getAmount(),
            'vatAmount' => $totalDiscountPrice->getVatAmount()->getAmount(),
        ];

        $addToCartResult = $addAnotherToCartResponse['data']['AddToCart'];

        $this->assertSame($totalPriceExpected, $addToCartResult['cart']['totalPrice']);
        $this->assertSame($totalDiscountPriceExpected, $addToCartResult['cart']['totalDiscountPrice']);
    }
}
