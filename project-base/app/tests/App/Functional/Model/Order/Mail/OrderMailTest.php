<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Mail;

use App\DataFixtures\Demo\AdditionalServiceDataFixture;
use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Mail\MailTemplate;
use App\Model\Mail\MailTemplateDataFactory;
use App\Model\Order\Mail\OrderMail;
use App\Model\Order\Order;
use App\Model\Order\OrderDataFactory;
use App\Model\Order\Status\OrderStatus;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Mail\MailDisplayPriceResolver;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail as BaseOrderMail;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentInstructionFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Shopsys\FrameworkBundle\Twig\HiddenPriceExtension;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
use Tests\App\Test\TransactionFunctionalTestCase;
use Twig\Environment;

class OrderMailTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrderItemPriceCalculation $orderItemPriceCalculation;

    /**
     * @inject
     */
    private MailTemplateDataFactory $mailTemplateDataFactory;

    /**
     * @inject
     */
    private DateTimeFormatterExtension $dateTimeFormatterExtension;

    /**
     * @inject
     */
    private BaseOrderMail $orderMail;

    /**
     * @inject
     */
    private OrderInputFactory $orderInputFactory;

    /**
     * @inject
     */
    private OrderProcessor $orderProcessor;

    /**
     * @inject
     */
    private OrderDataFactory $orderDataFactory;

    /**
     * @inject
     */
    private PlaceOrderFacade $placeOrderFacade;

    public function testGetMailTemplateNameByStatus(): void
    {
        $orderStatus1 = $this->getMockBuilder(OrderStatus::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatus1->expects($this->atLeastOnce())->method('getId')->willReturn(1);

        $orderStatus2 = $this->getMockBuilder(OrderStatus::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatus2->expects($this->atLeastOnce())->method('getId')->willReturn(2);

        $mailTempleteName1 = OrderMail::getMailTemplateNameByStatus($orderStatus1);
        $mailTempleteName2 = OrderMail::getMailTemplateNameByStatus($orderStatus2);

        $this->assertNotEmpty($mailTempleteName1);
        $this->assertIsString($mailTempleteName1);

        $this->assertNotEmpty($mailTempleteName2);
        $this->assertIsString($mailTempleteName2);

        $this->assertNotSame($mailTempleteName1, $mailTempleteName2);
    }

    public function testGetMessageByOrder(): void
    {
        $orderMail = $this->createOrderMail();

        $order = $this->getReference('order_1', Order::class);

        $mailTemplate = $this->createMailTemplate('body');

        $messageData = $orderMail->createMessage($mailTemplate, $order);

        $this->assertInstanceOf(MessageData::class, $messageData);
        $this->assertSame($mailTemplate->getSubject(), $messageData->subject);
        $this->assertSame($mailTemplate->getBody(), $messageData->body);

        $expectedDeliveryDateReplacement = $messageData
            ->variablesReplacementsForBody[OrderMail::VARIABLE_EXPECTED_DELIVERY_DATE];
        $this->assertInstanceOf(Closure::class, $expectedDeliveryDateReplacement);
        $this->assertSame('', $expectedDeliveryDateReplacement());
    }

    public function testExpectedDeliveryDateVariableIsFormattedInTheOrderDomainTimeZone(): void
    {
        $orderDomainTimeZone = new DateTimeZone('Pacific/Auckland');
        $expectedDeliveryDateStoredInUtc = new DateTimeImmutable('2026-09-10 00:00:00', $orderDomainTimeZone)
            ->setTimezone(new DateTimeZone('UTC'));
        $orderMail = $this->createOrderMail(
            $this->dateTimeFormatterExtension,
            $this->createDomainStub('en', $orderDomainTimeZone),
        );

        $orderStub = $this->createStub(Order::class);
        $orderStub->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);
        $orderStub->method('getEmail')->willReturn('customer@example.com');
        $orderStub->method('getExpectedDeliveryDate')->willReturn($expectedDeliveryDateStoredInUtc);
        $orderStub->method('getItems')->willReturn([]);

        $mailTemplate = $this->createMailTemplate(OrderMail::VARIABLE_EXPECTED_DELIVERY_DATE);

        $messageData = $orderMail->createMessage($mailTemplate, $orderStub);
        $expectedDeliveryDateReplacement = $messageData
            ->variablesReplacementsForBody[OrderMail::VARIABLE_EXPECTED_DELIVERY_DATE];

        $this->assertInstanceOf(Closure::class, $expectedDeliveryDateReplacement);
        $this->assertSame('Sep 10, 2026', $expectedDeliveryDateReplacement());
    }

    public function testProductsVariableShowsGiftSeparatelyAndAdditionalServiceUnderItsProduct(): void
    {
        $order = $this->createOrderWithGiftAndAdditionalService();
        $productItem = array_first($order->getProductItems());
        $giftItem = array_first($order->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT_GIFT));
        $additionalServiceItem = array_first($order->getItemsByType(OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE));
        $this->assertNotNull($giftItem, 'The order has to contain a gift, otherwise the test proves nothing');
        $this->assertNotNull($additionalServiceItem, 'The order has to contain an additional service, otherwise the test proves nothing');

        $messageData = $this->orderMail->createMessage($this->createMailTemplate(OrderMail::VARIABLE_PRODUCTS), $order);
        $productsHtml = $messageData->variablesReplacementsForBody[OrderMail::VARIABLE_PRODUCTS]();

        $additionalServicesHeading = t('Additional services', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $giftHeading = t('Gift with purchase', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());

        $productPosition = strpos($productsHtml, htmlspecialchars($productItem->getName()));
        $additionalServicesHeadingPosition = strpos($productsHtml, htmlspecialchars($additionalServicesHeading));
        $additionalServicePosition = strpos($productsHtml, htmlspecialchars($additionalServiceItem->getName()));
        $giftHeadingPosition = strpos($productsHtml, htmlspecialchars($giftHeading));
        $giftPosition = strpos($productsHtml, htmlspecialchars($giftItem->getName()));

        $this->assertIsInt($productPosition);
        $this->assertIsInt($additionalServicesHeadingPosition);
        $this->assertIsInt($additionalServicePosition);
        $this->assertIsInt($giftHeadingPosition);
        $this->assertIsInt($giftPosition);
        $this->assertLessThan($additionalServicesHeadingPosition, $productPosition);
        $this->assertLessThan($additionalServicePosition, $additionalServicesHeadingPosition);
        $this->assertLessThan($giftPosition, $giftHeadingPosition);
        $this->assertSame(1, substr_count($productsHtml, htmlspecialchars($giftHeading)));
        $this->assertStringNotContainsString(htmlspecialchars($order->getTransportItem()->getName()), $productsHtml);
        $this->assertStringNotContainsString(htmlspecialchars($order->getPaymentItem()->getName()), $productsHtml);
    }

    public function testProductsVariableShowsDiscountSharedByProductsOnceUnderFirstProduct(): void
    {
        $order = $this->createOrderWithNominalPromoCodeForTwoProducts();
        $productItems = array_values($order->getProductItems());
        $discountItem = array_first($order->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT));
        $this->assertCount(2, $productItems);
        $this->assertNotNull($discountItem, 'The order has to contain a discount, otherwise the test proves nothing');

        $messageData = $this->orderMail->createMessage($this->createMailTemplate(OrderMail::VARIABLE_PRODUCTS), $order);
        $productsHtml = $messageData->variablesReplacementsForBody[OrderMail::VARIABLE_PRODUCTS]();

        $firstProductPosition = strpos($productsHtml, htmlspecialchars($productItems[0]->getName()));
        $discountPosition = strpos($productsHtml, htmlspecialchars($discountItem->getName()));
        $secondProductPosition = strpos($productsHtml, htmlspecialchars($productItems[1]->getName()));

        $this->assertIsInt($firstProductPosition);
        $this->assertIsInt($discountPosition);
        $this->assertIsInt($secondProductPosition);
        $this->assertSame(1, substr_count($productsHtml, htmlspecialchars($discountItem->getName())));
        $this->assertLessThan($discountPosition, $firstProductPosition);
        $this->assertLessThan($secondProductPosition, $discountPosition);
    }

    private function createOrderWithGiftAndAdditionalService(): BaseOrder
    {
        $orderInput = $this->createOrderInput();

        $productWithAdditionalService = new QuantifiedProduct($this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class), 1);
        $productWithAdditionalService->setAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY, CartItemTypeEnum::TYPE_PRODUCT);
        $productWithAdditionalService->setAdditionalData(QuantifiedProduct::ADDITIONAL_SERVICES_KEY, [
            $this->getReference(AdditionalServiceDataFixture::ADDITIONAL_SERVICE_ASSEMBLY, AdditionalService::class),
        ]);
        $orderInput->addQuantifiedProduct($productWithAdditionalService);

        $gift = new QuantifiedProduct($this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class), 1);
        $gift->setAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY, CartItemTypeEnum::TYPE_PRODUCT_GIFT);
        $orderInput->addQuantifiedProduct($gift);

        return $this->placeOrder($orderInput);
    }

    private function createOrderWithNominalPromoCodeForTwoProducts(): BaseOrder
    {
        $orderInput = $this->createOrderInput();

        foreach (['1', '2'] as $productId) {
            $quantifiedProduct = new QuantifiedProduct($this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class), 10);
            $quantifiedProduct->setAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY, CartItemTypeEnum::TYPE_PRODUCT);
            $orderInput->addQuantifiedProduct($quantifiedProduct);
        }

        $orderInput->addPromoCode($this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE_NOMINAL, Domain::FIRST_DOMAIN_ID, PromoCode::class));

        return $this->placeOrder($orderInput);
    }

    private function createOrderInput(): OrderInput
    {
        $orderInput = $this->orderInputFactory->create($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID));
        $orderInput->setTransport($this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class));
        $orderInput->setPayment($this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY, Payment::class));

        return $orderInput;
    }

    private function placeOrder(OrderInput $orderInput): BaseOrder
    {
        $orderData = $this->orderDataFactory->create();
        $orderData->status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $orderData->firstName = 'firstName';
        $orderData->lastName = 'lastName';
        $orderData->email = 'email@example.com';
        $orderData->telephone = new PhoneData(number: 'telephone');
        $orderData->street = 'street';
        $orderData->city = 'city';
        $orderData->postcode = 'postcode';
        $orderData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->domainId = Domain::FIRST_DOMAIN_ID;
        $orderData->fillCurrencyFieldsFromCurrency($this->getFirstDomainCurrency());

        return $this->placeOrderFacade->createOrderOnly($this->orderProcessor->process($orderInput, $orderData));
    }

    private function createDomainStub(string $locale, DateTimeZone $dateTimeZone): Domain
    {
        $domainConfig = new DomainConfig(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            'example',
            $locale,
            $dateTimeZone,
            'https://example.com',
        );

        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getDomainConfigById')->willReturn($domainConfig);

        return $domainStub;
    }

    private function createOrderMail(
        ?DateTimeFormatterExtension $dateTimeFormatterExtension = null,
        ?Domain $domain = null,
    ): OrderMail {
        $routerStub = $this->createStub(DomainRouter::class);
        $routerStub->method('generate')->willReturn('generatedUrl');

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);
        $domainRouterFactoryStub->method('getRouter')->willReturn($routerStub);

        $settingStub = $this->createStub(Setting::class);
        $settingStub->method('getForDomain')->willReturn('no-reply@shopsys.com');

        return new OrderMail(
            $settingStub,
            $domainRouterFactoryStub,
            $this->createStub(Environment::class),
            $this->orderItemPriceCalculation,
            $domain ?? $this->domain,
            $this->createStub(PriceExtension::class),
            $dateTimeFormatterExtension ?? $this->createStub(DateTimeFormatterExtension::class),
            $this->createStub(OrderUrlGenerator::class),
            $this->createStub(HiddenPriceExtension::class),
            $this->createStub(PaymentInstructionFacade::class),
            $this->createStub(MailDisplayPriceResolver::class),
            $this->createStub(WithdrawalRequestFacade::class),
        );
    }

    private function createMailTemplate(string $body): MailTemplate
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->subject = 'subject';
        $mailTemplateData->body = $body;

        return new MailTemplate('templateName', Domain::FIRST_DOMAIN_ID, $mailTemplateData);
    }
}
