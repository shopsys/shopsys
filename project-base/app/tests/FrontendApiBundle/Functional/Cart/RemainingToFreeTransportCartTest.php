<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\SettingValueDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Tests\FrontendApiBundle\Functional\Order\OrderTestTrait;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RemainingToFreeTransportCartTest extends GraphQlTestCase
{
    use OrderTestTrait;

    private Product $testingProduct;

    /**
     * @inject
     */
    private PricingSetting $pricingSetting;

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
                    remainingAmountWithVatForFreeTransport
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart']['cart'];

        self::assertNull(
            $newlyCreatedCart['remainingAmountWithVatForFreeTransport'],
            'Actual remaining price has to be null for disabled free transport and payment',
        );

        $query = '{
            cart(
                cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}
            ) {
                remainingAmountWithVatForFreeTransport
            }
        }';

        $response = $this->getResponseContentForQuery($query);
        $cart = $response['data']['cart'];

        self::assertNull(
            $cart['remainingAmountWithVatForFreeTransport'],
            'Actual remaining price has to be null for disabled free transport and payment',
        );
    }

    private function disableFreeTransportAndPayment(): void
    {
        $this->pricingSetting->setFreeTransportAndPaymentPriceLimit($this->domain->getId(), null);
    }

    public function testCorrectRemainingPriceIsReturned(): void
    {
        $freeTransportAndPaymentLimit = Money::create(SettingValueDataFixture::FREE_TRANSPORT_AND_PAYMENT_LIMIT);

        $mutation = 'mutation {
            AddToCart(
                input: {
                    productUuid: "' . $this->testingProduct->getUuid() . '"
                    quantity: 1
                }
            ) {
                cart {
                    uuid
                    totalItemsPrice{
                        priceWithVat
                    }
                    remainingAmountWithVatForFreeTransport
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart']['cart'];

        $totalItemsPriceWithVat = Money::create($newlyCreatedCart['totalItemsPrice']['priceWithVat']);
        $expectedRemainingPrice = $freeTransportAndPaymentLimit->subtract($totalItemsPriceWithVat);

        self::assertTrue(
            $expectedRemainingPrice->equals(Money::create($newlyCreatedCart['remainingAmountWithVatForFreeTransport'])),
            sprintf(
                'Actual remaining price (%s) is different than expected (%s)',
                $expectedRemainingPrice->getAmount(),
                $newlyCreatedCart['remainingAmountWithVatForFreeTransport'],
            ),
        );

        $newlyCreatedCartUuid = $newlyCreatedCart['uuid'];
        $this->addCardPaymentToCart($newlyCreatedCartUuid);
        $this->addPplTransportToCart($newlyCreatedCartUuid);

        $query = '{
            cart(
                cartInput: {cartUuid: "' . $newlyCreatedCartUuid . '"}
            ) {
                remainingAmountWithVatForFreeTransport
                totalItemsPrice {
                    priceWithVat
                }
            }
        }';

        $response = $this->getResponseContentForQuery($query);
        $cart = $response['data']['cart'];

        $totalItemsPriceWithVat = Money::create($cart['totalItemsPrice']['priceWithVat']);
        $expectedRemainingPriceAfterAddingTransportAndPayment = $freeTransportAndPaymentLimit->subtract($totalItemsPriceWithVat);

        self::assertTrue(
            $expectedRemainingPriceAfterAddingTransportAndPayment->equals(Money::create($cart['remainingAmountWithVatForFreeTransport'])),
            sprintf(
                'Actual remaining price (%s) is different than expected (%s)',
                $expectedRemainingPriceAfterAddingTransportAndPayment->getAmount(),
                $newlyCreatedCart['remainingAmountWithVatForFreeTransport'],
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
                    remainingAmountWithVatForFreeTransport
                }
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart']['cart'];

        self::assertTrue(
            Money::create($newlyCreatedCart['remainingAmountWithVatForFreeTransport'])->isZero(),
            sprintf(
                'Actual remaining price (%s) should be zero',
                $newlyCreatedCart['remainingAmountWithVatForFreeTransport'],
            ),
        );

        $query = '{
            cart(
                cartInput: {cartUuid: "' . $newlyCreatedCart['uuid'] . '"}
            ) {
                remainingAmountWithVatForFreeTransport
                totalPrice {
                    priceWithVat
                }
            }
        }';

        $response = $this->getResponseContentForQuery($query);
        $cart = $response['data']['cart'];

        self::assertTrue(
            Money::create($cart['remainingAmountWithVatForFreeTransport'])->isZero(),
            sprintf(
                'Actual remaining price (%s) should be zero',
                $cart['remainingAmountWithVatForFreeTransport'],
            ),
        );
    }
}
