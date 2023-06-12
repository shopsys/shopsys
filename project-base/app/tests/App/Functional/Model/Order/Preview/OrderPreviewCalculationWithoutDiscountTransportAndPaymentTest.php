<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Preview;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Order\Preview\OrderPreviewCalculation;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Tests\App\Test\TransactionFunctionalTestCase;

class OrderPreviewCalculationWithoutDiscountTransportAndPaymentTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrderPreviewCalculation $orderPreviewCalculation;

    public function testCalculatePreviewWithoutDiscountTransportAndPayment(): void
    {
        $currency = new Currency(new CurrencyData());

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $productQuantity = 5;

        $quantifiedProduct = new QuantifiedProduct(
            $product,
            $productQuantity,
        );

        $quantifiedProducts = [
            $quantifiedProduct,
        ];

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);

        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);

        /** @var \App\Model\Order\PromoCode\PromoCode $validPromoCode */
        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1);

        $orderPreview = $this->orderPreviewCalculation->calculatePreview(
            $currency,
            $this->domain->getId(),
            $quantifiedProducts,
            $transport,
            $payment,
            null,
            null,
            null,
            $validPromoCode,
        );

        $totalPrice = new Price(Money::createFromFloat(578.35, 2), Money::createFromFloat(699.80, 2));

        $this->assertEquals($totalPrice, $orderPreview->getTotalPriceWithoutDiscountTransportAndPayment());
    }
}
