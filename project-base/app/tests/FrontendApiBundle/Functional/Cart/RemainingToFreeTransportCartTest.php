<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\SettingValueDataFixture;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Tests\FrontendApiBundle\Functional\Order\OrderTestTrait;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RemainingToFreeTransportCartTest extends GraphQlTestCase
{
    use OrderTestTrait;

    private Product $testingProduct;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
    }

    public function testNullIsReturnedWhenNotEnabled(): void
    {
        $this->disableFreeTransportAndPayment();

        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: 1
                }
            ) {
                cart {
                    uuid
                    remainingAmountForFreeTransport
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart']['cart'];

        self::assertNull(
            $newlyCreatedCart['remainingAmountForFreeTransport'],
            'Actual remaining price has to be null for disabled free transport and payment',
        );

        $query = '{
            cart(
                cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}
            ) {
                remainingAmountForFreeTransport
            }
        }';

        $response = $this->getResponseContentForQuery($query);
        $cart = $response['data']['cart'];

        self::assertNull(
            $cart['remainingAmountForFreeTransport'],
            'Actual remaining price has to be null for disabled free transport and payment',
        );
    }

    private function disableFreeTransportAndPayment(): void
    {
        $this->pricingSetting->setFreeTransportAndPaymentPriceLimit($this->domain->getId(), null);
    }

    public function testCorrectRemainingPriceIsReturned(): void
    {
        $freeTransportAndPaymentLimit = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(
            Money::create(SettingValueDataFixture::FREE_TRANSPORT_AND_PAYMENT_LIMIT),
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK),
            Domain::FIRST_DOMAIN_ID,
        );

        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: 1
                }
            ) {
                cart {
                    uuid
                    totalItemsPrice {
                        priceWithVat
                        priceWithoutVat
                    }
                    remainingAmountForFreeTransport
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart']['cart'];

        if ($this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $totalItemsPriceWithVat = Money::create($newlyCreatedCart['totalItemsPrice']['priceWithVat']);
            $expectedRemainingPrice = $freeTransportAndPaymentLimit->subtract($totalItemsPriceWithVat);
        } else {
            $totalItemsPriceWithoutVat = Money::create($newlyCreatedCart['totalItemsPrice']['priceWithoutVat']);
            $expectedRemainingPrice = $freeTransportAndPaymentLimit->subtract($totalItemsPriceWithoutVat);
        }

        self::assertTrue(
            $expectedRemainingPrice->equals(Money::create($newlyCreatedCart['remainingAmountForFreeTransport'])),
            sprintf(
                'Actual remaining price (%s) is different than expected (%s)',
                $expectedRemainingPrice->getAmount(),
                $newlyCreatedCart['remainingAmountForFreeTransport'],
            ),
        );

        $newlyCreatedCartUuid = $newlyCreatedCart['uuid'];
        $this->addCardPaymentToCart($newlyCreatedCartUuid);
        $this->addPplTransportToCart($newlyCreatedCartUuid);

        $query = '{
            cart(
                cartInput: {cartUuid: "' . $newlyCreatedCartUuid . '"}
            ) {
                remainingAmountForFreeTransport
                totalItemsPrice {
                    priceWithVat
                    priceWithoutVat
                }
            }
        }';

        $response = $this->getResponseContentForQuery($query);
        $cart = $response['data']['cart'];

        if ($this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $totalItemsPriceWithVat = Money::create($newlyCreatedCart['totalItemsPrice']['priceWithVat']);
            $expectedRemainingPriceAfterAddingTransportAndPayment = $freeTransportAndPaymentLimit->subtract($totalItemsPriceWithVat);
        } else {
            $totalItemsPriceWithoutVat = Money::create($newlyCreatedCart['totalItemsPrice']['priceWithoutVat']);
            $expectedRemainingPriceAfterAddingTransportAndPayment = $freeTransportAndPaymentLimit->subtract($totalItemsPriceWithoutVat);
        }

        self::assertTrue(
            $expectedRemainingPriceAfterAddingTransportAndPayment->equals(Money::create($cart['remainingAmountForFreeTransport'])),
            sprintf(
                'Actual remaining price (%s) is different than expected (%s)',
                $expectedRemainingPriceAfterAddingTransportAndPayment->getAmount(),
                $newlyCreatedCart['remainingAmountForFreeTransport'],
            ),
        );

        self::assertTrue(
            $expectedRemainingPriceAfterAddingTransportAndPayment->equals($expectedRemainingPrice),
            sprintf(
                'Remaining price after adding transport and payment (%s) differs from the original remaining price (%s)',
                $expectedRemainingPriceAfterAddingTransportAndPayment->getAmount(),
                $expectedRemainingPrice->getAmount(),
            ),
        );
    }

    public function testZeroIsReturnedWhenPriceIsHigherThenLimit(): void
    {
        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: 100
                }
            ) {
                cart {
                    uuid
                    remainingAmountForFreeTransport
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart']['cart'];

        self::assertTrue(
            Money::create($newlyCreatedCart['remainingAmountForFreeTransport'])->isZero(),
            sprintf(
                'Actual remaining price (%s) should be zero',
                $newlyCreatedCart['remainingAmountForFreeTransport'],
            ),
        );

        $query = '{
            cart(
                cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}
            ) {
                remainingAmountForFreeTransport
                totalPrice {
                    priceWithVat
                }
            }
        }';

        $response = $this->getResponseContentForQuery($query);
        $cart = $response['data']['cart'];

        self::assertTrue(
            Money::create($cart['remainingAmountForFreeTransport'])->isZero(),
            sprintf(
                'Actual remaining price (%s) should be zero',
                $cart['remainingAmountForFreeTransport'],
            ),
        );
    }
}
