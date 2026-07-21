<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\GiftVoucher;

use App\DataFixtures\Demo\GiftVoucherDataFixture;
use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherCodeGenerator;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherDataFactory;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherGenerationFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherRepository;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherStatusEnum;
use Shopsys\FrameworkBundle\Model\Order\Messenger\OrderMarkedAsPaidMessage;
use Shopsys\FrameworkBundle\Model\Order\Messenger\OrderMarkedAsPaidMessageGiftVoucherHandler;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderPaidStatusFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Shopsys\FrameworkBundle\Model\Transport\TransportUnavailabilityReasonInCartEnum;
use Shopsys\FrontendApiBundle\Component\Constraints\TransportInCart;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mailer\SentMessage;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

final class GiftVoucherOrderFlowTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private OrderPaidStatusFacade $orderPaidStatusFacade;

    /**
     * @inject
     */
    private OrderMarkedAsPaidMessageGiftVoucherHandler $orderMarkedAsPaidMessageGiftVoucherHandler;

    /**
     * @inject
     */
    private GiftVoucherRepository $giftVoucherRepository;

    /**
     * @inject
     */
    private GiftVoucherGenerationFacade $giftVoucherGenerationFacade;

    /**
     * @inject
     */
    private ProductPriceCalculation $productPriceCalculation;

    /**
     * @inject
     */
    private GiftVoucherFacade $giftVoucherFacade;

    /**
     * @inject
     */
    private GiftVoucherDataFactory $giftVoucherDataFactory;

    /**
     * @inject
     */
    private DelayedEnvelopesCollector $delayedEnvelopesCollector;

    public function testGiftVouchersAreGeneratedAfterVoucherOnlyOrderIsMarkedAsPaid(): void
    {
        $order = $this->createOrderWithElectronicGiftVouchers(quantity: 2);

        self::assertTrue($order->getTransport()->isEmailType());
        self::assertFalse($order->isPaid());

        $this->orderPaidStatusFacade->markOrderAsPaid($order);

        self::assertTrue($order->isPaid());

        ($this->orderMarkedAsPaidMessageGiftVoucherHandler)(new OrderMarkedAsPaidMessage($order->getId()));

        $giftVouchers = $this->giftVoucherRepository->getAllCreatedOnOrder($order);

        self::assertCount(2, $giftVouchers);

        $voucherOrderItem = array_first($order->getProductItems());
        $voucherOrderItemUnitPriceWithVat = $voucherOrderItem->getUnitPriceWithVat();

        foreach ($giftVouchers as $giftVoucher) {
            self::assertSame(GiftVoucherCodeGenerator::CODE_LENGTH, strlen($giftVoucher->getCode()));
            self::assertMatchesRegularExpression(
                '/^[' . GiftVoucherCodeGenerator::CODE_ALPHABET . ']+$/',
                $giftVoucher->getCode(),
            );
            self::assertTrue($giftVoucher->isUnredeemed());
            self::assertSame($order->getEmail(), $giftVoucher->getCustomerEmail());
            self::assertSame(ProductDataFixture::PRODUCT_CATNUM_ELECTRONIC_GIFT_VOUCHER_1000, $giftVoucher->getProductCatnum());
            self::assertSame($voucherOrderItem->getName(), $giftVoucher->getProductName());
            self::assertSame($order->getDomainId(), $giftVoucher->getDomainId());
            self::assertTrue($giftVoucher->getValueWithVat()->equals($voucherOrderItemUnitPriceWithVat));
            self::assertSame($voucherOrderItem->getVatPercent(), $giftVoucher->getVatPercent());
        }
    }

    public function testGeneratedGiftVoucherValueComesFromPaidOrderItemPriceNotFromCurrentSellingPrice(): void
    {
        $order = $this->createOrderWithElectronicGiftVouchers(quantity: 1);
        $voucherProduct = $this->getElectronicGiftVoucherProduct();
        $customerPricingGroup = $order->getCustomerUser()->getPricingGroup();

        $manualInputPrice = $this->em->getRepository(ProductManualInputPrice::class)->findOneBy([
            'product' => $voucherProduct,
            'pricingGroup' => $customerPricingGroup,
        ]);
        $originalInputPrice = $manualInputPrice->getInputPrice();
        $manualInputPrice->setInputPrice($originalInputPrice->add($originalInputPrice));
        $this->em->flush();

        $this->orderPaidStatusFacade->markOrderAsPaid($order);
        ($this->orderMarkedAsPaidMessageGiftVoucherHandler)(new OrderMarkedAsPaidMessage($order->getId()));

        $giftVoucher = array_first($this->giftVoucherRepository->getAllCreatedOnOrder($order));
        $paidUnitPriceWithVat = array_first($order->getProductItems())->getUnitPriceWithVat();
        $currentSellingPriceWithVat = $this->productPriceCalculation
            ->calculatePrice($voucherProduct, $order->getDomainId(), $customerPricingGroup)
            ->getPrice()
            ->getPriceWithVat();

        self::assertFalse($currentSellingPriceWithVat->equals($paidUnitPriceWithVat));
        self::assertTrue($giftVoucher->getValueWithVat()->equals($paidUnitPriceWithVat));
    }

    public function testGiftVouchersAreNotGeneratedTwiceForTheSameOrder(): void
    {
        $order = $this->createOrderWithElectronicGiftVouchers(quantity: 1);
        $this->orderPaidStatusFacade->markOrderAsPaid($order);

        ($this->orderMarkedAsPaidMessageGiftVoucherHandler)(new OrderMarkedAsPaidMessage($order->getId()));
        ($this->orderMarkedAsPaidMessageGiftVoucherHandler)(new OrderMarkedAsPaidMessage($order->getId()));

        self::assertCount(1, $this->giftVoucherRepository->getAllCreatedOnOrder($order));
    }

    public function testGenerationForOrderWithGeneratedGiftVouchersReturnsThemForMailDeliveryRetry(): void
    {
        $order = $this->createOrderWithElectronicGiftVouchers(quantity: 2);
        $this->orderPaidStatusFacade->markOrderAsPaid($order);

        ($this->orderMarkedAsPaidMessageGiftVoucherHandler)(new OrderMarkedAsPaidMessage($order->getId()));

        $existingGiftVoucherIds = array_map(
            static fn (GiftVoucher $giftVoucher) => $giftVoucher->getId(),
            $this->giftVoucherRepository->getAllCreatedOnOrder($order),
        );
        $returnedGiftVoucherIds = array_map(
            static fn (GiftVoucher $giftVoucher) => $giftVoucher->getId(),
            $this->giftVoucherGenerationFacade->generateForOrder($order),
        );

        self::assertNotSame([], $returnedGiftVoucherIds);
        self::assertSame($existingGiftVoucherIds, $returnedGiftVoucherIds);
    }

    public function testOrderFullyCoveredByGiftVoucherIsRedeemedAndMarkedAsPaidImmediately(): void
    {
        $this->addProductToCart($this->getProductByCatnum(ProductDataFixture::PRODUCT_CATNUM_A4TECH_MOUSE));
        $this->selectTransportAndPaymentInCart(TransportDataFixture::TRANSPORT_CZECH_POST, PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);

        $giftVoucher = $this->createUnredeemedGiftVoucherWithValue($this->getCurrentCartTotalPriceWithVat());
        $this->applyGiftVoucherToCart($giftVoucher->getCode());

        $data = $this->getResponseDataForGraphQlType($this->createOrderFromCurrentCart(), 'CreateOrder');
        $order = $this->orderFacade->getByUuid($data['order']['uuid']);
        $giftVoucher = $this->giftVoucherFacade->getById($giftVoucher->getId());

        self::assertTrue($order->isPaid());
        self::assertTrue($order->getRemainingToPay()->isZero());
        self::assertFalse($giftVoucher->isUnredeemed());
        self::assertSame($order->getId(), $giftVoucher->getRedeemedOnOrder()?->getId());
        self::assertNotNull($giftVoucher->getRedeemedAt());
    }

    public function testOrderIsNotPlacedWhenGiftVouchersExceedPayableAmount(): void
    {
        $this->addProductToCart($this->getProductByCatnum(ProductDataFixture::PRODUCT_CATNUM_A4TECH_MOUSE));
        $this->applyGiftVoucherToCart(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);
        $this->selectTransportAndPaymentInCart(TransportDataFixture::TRANSPORT_CZECH_POST, PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);

        $response = $this->createOrderFromCurrentCart();

        $this->assertUserError($response, 'gift-vouchers-exceed-payable-amount');

        $giftVoucher = $this->getReference(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED, GiftVoucher::class);

        self::assertTrue($giftVoucher->isUnredeemed());
    }

    public function testGiftVoucherExpiringBeforeCheckoutIsRemovedFromCartWithModification(): void
    {
        $this->addProductToCart($this->getProductByCatnum(ProductDataFixture::PRODUCT_CATNUM_A4TECH_MOUSE));
        $this->selectTransportAndPaymentInCart(TransportDataFixture::TRANSPORT_CZECH_POST, PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);

        $giftVoucher = $this->createUnredeemedGiftVoucherWithValue($this->getCurrentCartTotalPriceWithVat());
        $this->applyGiftVoucherToCart($giftVoucher->getCode());

        $expiredGiftVoucherData = $this->giftVoucherDataFactory->createFromGiftVoucher($giftVoucher);
        $expiredGiftVoucherData->validUntil = (new DateTimeImmutable())->modify('-1 second');
        $this->giftVoucherFacade->edit($giftVoucher->getId(), $expiredGiftVoucherData);

        $data = $this->getResponseDataForGraphQlType($this->createOrderFromCurrentCart(), 'CreateOrder');

        self::assertFalse($data['orderCreated']);
        self::assertSame(
            [$giftVoucher->getCode()],
            $data['cart']['modifications']['giftVoucherModifications']['noLongerApplicableGiftVouchers'],
        );
    }

    public function testGiftVoucherEmailIsNotSentTwiceForDuplicateMarkedAsPaidMessage(): void
    {
        $order = $this->createOrderWithElectronicGiftVouchers(quantity: 1);
        $this->orderPaidStatusFacade->markOrderAsPaid($order);

        ($this->orderMarkedAsPaidMessageGiftVoucherHandler)(new OrderMarkedAsPaidMessage($order->getId()));

        $sendEmailMessages = $this->popCollectedSendEmailMessages();

        self::assertCount(1, $sendEmailMessages);

        $giftVoucher = array_first($this->giftVoucherRepository->getAllCreatedOnOrder($order));

        self::assertNotNull($giftVoucher->getEmailEnqueuedAt());
        self::assertNull($giftVoucher->getEmailSentAt());

        $this->dispatchSentMessageEventFor(array_first($sendEmailMessages));

        self::assertNotNull($giftVoucher->getEmailSentAt());

        ($this->orderMarkedAsPaidMessageGiftVoucherHandler)(new OrderMarkedAsPaidMessage($order->getId()));

        self::assertSame(0, $this->popCollectedSendEmailMessagesCount());
    }

    public function testOrderIsNotPlacedWhenAppliedGiftVoucherIsNoLongerRedeemable(): void
    {
        $this->addProductToCart($this->getProductByCatnum(ProductDataFixture::PRODUCT_CATNUM_A4TECH_MOUSE));
        $this->applyGiftVoucherToCart(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);
        $this->selectTransportAndPaymentInCart(TransportDataFixture::TRANSPORT_CZECH_POST, PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);

        $giftVoucher = $this->getReference(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED, GiftVoucher::class);
        $orderRedeemingGiftVoucherInTheMeantime = $this->getReference(OrderDataFixture::ORDER_PREFIX . '1', Order::class);
        $giftVoucher->markAsRedeemed($orderRedeemingGiftVoucherInTheMeantime, new DateTimeImmutable());
        $this->em->flush();

        $data = $this->getResponseDataForGraphQlType($this->createOrderFromCurrentCart(), 'CreateOrder');

        self::assertFalse($data['orderCreated']);
        self::assertSame(
            [GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE],
            $data['cart']['modifications']['giftVoucherModifications']['noLongerApplicableGiftVouchers'],
        );
    }

    public function testEmailTransportIsRejectedForCartWithRegularProduct(): void
    {
        $regularProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->addProductToCart($regularProduct);

        $emailTransport = $this->getReference(TransportDataFixture::TRANSPORT_EMAIL, Transport::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'transportUuid' => $emailTransport->getUuid(),
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        self::assertSame(TransportInCart::UNAVAILABLE_TRANSPORT_ERROR, $validationErrors['input'][0]['code']);
    }

    public function testEmailTransportIsNotOfferedForCartWithRegularProduct(): void
    {
        $regularProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->addProductToCart($regularProduct);

        self::assertNotContains(TransportTypeEnum::TYPE_EMAIL, $this->getOfferedTransportTypeCodes());
    }

    public function testEmailTransportIsNotOfferedForCartMixingGiftVoucherWithRegularProduct(): void
    {
        $voucherProduct = $this->getElectronicGiftVoucherProduct();
        $this->addProductToCart($voucherProduct);
        $regularProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->addProductToCart($regularProduct);

        self::assertNotContains(TransportTypeEnum::TYPE_EMAIL, $this->getOfferedTransportTypeCodes());
    }

    public function testEmailTransportIsOfferedForCartWithElectronicGiftVouchersOnly(): void
    {
        $voucherProduct = $this->getElectronicGiftVoucherProduct();
        $this->addProductToCart($voucherProduct);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportTypeCodesQuery.graphql');
        $transports = $this->getResponseDataForGraphQlType($response, 'transports');

        self::assertContains(TransportTypeEnum::TYPE_EMAIL, array_column($transports, 'transportTypeCode'));

        foreach ($transports as $transport) {
            if ($transport['transportTypeCode'] === TransportTypeEnum::TYPE_EMAIL) {
                self::assertSame([], $transport['productsBlockingSelectionInCart']);

                continue;
            }

            self::assertSame(
                [TransportUnavailabilityReasonInCartEnum::ELECTRONIC_GIFT_VOUCHER_ONLY],
                array_column($transport['productsBlockingSelectionInCart'], 'reason'),
            );
        }
    }

    /**
     * @return string[]
     */
    private function getOfferedTransportTypeCodes(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportTypeCodesQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'transports');

        return array_column($responseData, 'transportTypeCode');
    }

    private function getElectronicGiftVoucherProduct(): Product
    {
        return $this->getReference(ProductDataFixture::PRODUCT_ELECTRONIC_GIFT_VOUCHER_1000, Product::class);
    }

    private function createUnredeemedGiftVoucherWithValue(Money $value): GiftVoucher
    {
        $domainId = $this->domain->getId();

        $giftVoucherData = $this->giftVoucherDataFactory->create();
        $giftVoucherData->domainId = $domainId;
        $giftVoucherData->valueWithVat = $value;
        $giftVoucherData->currencyCode = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId)->getCode();
        $giftVoucherData->status = GiftVoucherStatusEnum::STATUS_UNREDEEMED;
        $giftVoucherData->activatedAt = new DateTimeImmutable();
        $giftVoucherData->validUntil = $giftVoucherData->activatedAt->modify(GiftVoucherGenerationFacade::VALIDITY_MODIFIER);

        return $this->giftVoucherFacade->create($giftVoucherData);
    }

    private function getCurrentCartTotalPriceWithVat(): Money
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../Cart/graphql/CartPricesQuery.graphql', [
            'cartUuid' => null,
        ]);

        return Money::create($this->getResponseDataForGraphQlType($response, 'cart')['totalPrice']['priceWithVat']);
    }

    private function popCollectedSendEmailMessagesCount(): int
    {
        return count($this->popCollectedSendEmailMessages());
    }

    /**
     * @return \Symfony\Component\Mailer\Messenger\SendEmailMessage[]
     */
    private function popCollectedSendEmailMessages(): array
    {
        $sendEmailMessages = [];

        foreach ($this->delayedEnvelopesCollector->popEnvelopes() as $envelope) {
            if ($envelope->getMessage() instanceof SendEmailMessage) {
                $sendEmailMessages[] = $envelope->getMessage();
            }
        }

        return $sendEmailMessages;
    }

    private function dispatchSentMessageEventFor(SendEmailMessage $sendEmailMessage): void
    {
        $message = $sendEmailMessage->getMessage();
        $envelope = $sendEmailMessage->getEnvelope() ?? Envelope::create($message);

        $this->eventDispatcher->dispatch(new SentMessageEvent(new SentMessage($message, $envelope)));
    }

    private function getProductByCatnum(string $catnum): Product
    {
        return $this->em->getRepository(Product::class)->findOneBy(['catnum' => $catnum]);
    }

    private function createOrderWithElectronicGiftVouchers(int $quantity): Order
    {
        $this->addProductToCart($this->getElectronicGiftVoucherProduct(), $quantity);
        $this->selectTransportAndPaymentInCart(TransportDataFixture::TRANSPORT_EMAIL, PaymentDataFixture::PAYMENT_GOPAY_CARD);

        $data = $this->getResponseDataForGraphQlType($this->createOrderFromCurrentCart(), 'CreateOrder');

        return $this->orderFacade->getByUuid($data['order']['uuid']);
    }

    private function selectTransportAndPaymentInCart(
        string $transportReferenceName,
        string $paymentReferenceName,
    ): void {
        $transport = $this->getReference($transportReferenceName, Transport::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'transportUuid' => $transport->getUuid(),
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'ChangeTransportInCart');

        $payment = $this->getReference($paymentReferenceName, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'paymentUuid' => $payment->getUuid(),
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'ChangePaymentInCart');
    }

    private function applyGiftVoucherToCart(string $giftVoucherCode): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../Cart/graphql/ApplyGiftVoucherToCart.graphql', [
            'cartUuid' => null,
            'giftVoucherCode' => $giftVoucherCode,
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'ApplyGiftVoucherToCart');
    }

    /**
     * @return array<string, mixed>
     */
    private function createOrderFromCurrentCart(): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/CreateOrderMutation.graphql', [
            'cartUuid' => null,
            'city' => 'Ostrava',
            'companyName' => null,
            'companyNumber' => null,
            'companyTaxNumber' => null,
            'country' => 'CZ',
            'deliveryAddressUuid' => null,
            'deliveryCity' => null,
            'deliveryCompanyName' => null,
            'deliveryCountry' => null,
            'deliveryFirstName' => null,
            'deliveryLastName' => null,
            'deliveryPostcode' => null,
            'deliveryStreet' => null,
            'deliveryTelephone' => null,
            'email' => 'no-reply@shopsys.com',
            'firstName' => 'Jaromír',
            'lastName' => 'Jágr',
            'heurekaAgreement' => false,
            'isDeliveryAddressDifferentFromBilling' => false,
            'newsletterSubscription' => false,
            'note' => '',
            'onCompanyBehalf' => false,
            'postcode' => '70200',
            'street' => 'Hlubinská 10',
            'telephone' => new PhoneData('CZ', '+420', '605000123'),
        ]);
    }

    private function addProductToCart(Product $product, int $quantity = 1): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => $quantity,
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'AddToCart');
    }
}
