<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Processing;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Cart\Cart;
use App\Model\Cart\CartFacade;
use App\Model\Order\OrderData;
use App\Model\Order\OrderFacade;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider;
use Tests\App\Test\TransactionFunctionalTestCase;

final class PaidProductGiftCountsTowardsFreeTransportTest extends TransactionFunctionalTestCase
{
    private const string PRODUCT_WITH_GIFT_ID = '14';

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private TransportPriceProvider $transportPriceProvider;

    /**
     * @inject
     */
    private PaymentPriceProvider $paymentPriceProvider;

    /**
     * @inject
     */
    private CartTransportDataFactory $cartTransportDataFactory;

    /**
     * @inject
     */
    private CartPaymentDataFactory $cartPaymentDataFactory;

    /**
     * @inject
     */
    private PricingSetting $pricingSetting;

    /**
     * @inject
     */
    private GiftPlanSettingFacade $giftPlanSettingFacade;

    public function testOfferedTransportAndPaymentPricesMatchTheProcessedOrderWhenPaidGiftReachesFreeTransportLimit(): void
    {
        $domainId = $this->domain->getId();
        $domainConfig = $this->domain->getCurrentDomainConfig();
        $this->giftPlanSettingFacade->setInputGiftPrice(Money::create(100), $domainId);
        $this->resetRequestScopedCache();

        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY, Payment::class);
        $cart = $this->createCartWithProductAndItsGift($transport, $payment);

        $orderData = $this->orderFacade->createOrderDataFromCart($cart, $domainConfig);
        $giftsTotalPrice = $orderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_PRODUCT_GIFT]);
        $this->assertTrue(
            $this->getPriceForFreeTransportLimit($giftsTotalPrice)->isGreaterThan(Money::zero()),
            'The demo gift plan has to add a priced gift to the cart, otherwise the test proves nothing',
        );
        $productsTotalPriceWithoutGifts = $orderData->getProductsAndAdditionalServicesTotalPriceAfterAppliedDiscounts()->subtract($giftsTotalPrice);

        $this->pricingSetting->setFreeTransportAndPaymentPriceLimit(
            $domainId,
            $this->getPriceForFreeTransportLimit($productsTotalPriceWithoutGifts)->add(Money::create(1)),
        );
        $this->resetRequestScopedCache();

        $offeredTransportPrice = $this->transportPriceProvider->getTransportPrice($cart, $transport, $domainConfig);
        $offeredPaymentPrice = $this->paymentPriceProvider->getPaymentPrice($cart, $payment, $domainConfig);
        $processedOrderData = $this->orderFacade->createOrderDataFromCart($cart, $domainConfig);

        $this->assertPricesEqual($offeredTransportPrice, $this->getItemTypeTotalPrice($processedOrderData, OrderItemTypeEnum::TYPE_TRANSPORT), 'transport');
        $this->assertPricesEqual($offeredPaymentPrice, $this->getItemTypeTotalPrice($processedOrderData, OrderItemTypeEnum::TYPE_PAYMENT), 'payment');
        $this->assertTrue($offeredTransportPrice->getPriceWithVat()->isZero(), 'The paid gift pushes the cart over the free transport limit, so the transport has to be free');
        $this->assertTrue($offeredPaymentPrice->getPriceWithVat()->isZero(), 'The paid gift pushes the cart over the free transport limit, so the payment has to be free');
    }

    private function createCartWithProductAndItsGift(Transport $transport, Payment $payment): Cart
    {
        $cart = $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists(
            new CustomerUserIdentifier('paid-product-gift-free-transport-test'),
        );
        $productWithGift = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_WITH_GIFT_ID, Product::class);
        $this->cartFacade->addProductToExistingCart($productWithGift, 1, $cart);
        $cart->editCartTransport($this->cartTransportDataFactory->create($cart, $transport->getUuid(), null));
        $cart->editCartPayment($this->cartPaymentDataFactory->create($cart, $payment->getUuid(), null));

        return $cart;
    }

    private function getPriceForFreeTransportLimit(PriceInterface $price): Money
    {
        if ($this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            return $price->getPriceWithVat();
        }

        return $price->getPriceWithoutVat();
    }

    private function getItemTypeTotalPrice(OrderData $orderData, string $itemType): PriceInterface
    {
        return $orderData->getTotalPriceForItemTypes([$itemType]);
    }

    private function assertPricesEqual(
        PriceInterface $offeredPrice,
        PriceInterface $chargedPrice,
        string $itemName,
    ): void {
        $this->assertTrue(
            $offeredPrice->getPriceWithVat()->equals($chargedPrice->getPriceWithVat()),
            sprintf(
                'The offered %s price (%s) has to match the %s price of the processed order (%s)',
                $itemName,
                $offeredPrice->getPriceWithVat()->getAmount(),
                $itemName,
                $chargedPrice->getPriceWithVat()->getAmount(),
            ),
        );
    }
}
