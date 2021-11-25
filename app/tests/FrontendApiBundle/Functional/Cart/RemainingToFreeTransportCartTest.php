<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\SettingValueDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RemainingToFreeTransportCartTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private Product $testingProduct;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     * @inject
     */
    private PricingSetting $pricingSetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
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
                uuid
                remainingAmountWithVatForFreeTransport
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

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
                uuid
                totalPrice{
                    priceWithVat
                }
                remainingAmountWithVatForFreeTransport
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        $totalCartPriceWithVat = Money::create($newlyCreatedCart['totalPrice']['priceWithVat']);
        $expectedRemainingPrice = $freeTransportAndPaymentLimit->subtract($totalCartPriceWithVat);

        self::assertTrue(
            $expectedRemainingPrice->equals(Money::create($newlyCreatedCart['remainingAmountWithVatForFreeTransport'])),
            sprintf(
                'Actual remaining price (%s) is different than expected (%s)',
                $expectedRemainingPrice->getAmount(),
                $newlyCreatedCart['remainingAmountWithVatForFreeTransport']
            )
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
            $expectedRemainingPrice->equals(Money::create($cart['remainingAmountWithVatForFreeTransport'])),
            sprintf(
                'Actual remaining price (%s) is different than expected (%s)',
                $expectedRemainingPrice->getAmount(),
                $newlyCreatedCart['remainingAmountWithVatForFreeTransport']
            )
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
                uuid
                remainingAmountWithVatForFreeTransport
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        $newlyCreatedCart = $response['data']['AddToCart'];

        self::assertTrue(
            Money::create($newlyCreatedCart['remainingAmountWithVatForFreeTransport'])->isZero(),
            sprintf(
                'Actual remaining price (%s) should be zero',
                $newlyCreatedCart['remainingAmountWithVatForFreeTransport']
            )
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
                $cart['remainingAmountWithVatForFreeTransport']
            )
        );
    }
}
