<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Mail;

use App\Model\Mail\MailTemplate;
use App\Model\Mail\MailTemplateDataFactory;
use App\Model\Order\Mail\OrderMail;
use App\Model\Order\Order;
use App\Model\Order\Status\OrderStatus;
use Closure;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailDisplayPriceResolver;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentInstructionFacade;
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

    public function testExpectedDeliveryDateVariableUsesLocalizedOrderSnapshot(): void
    {
        $expectedDeliveryDate = new DateTimeImmutable('2026-09-09T22:00:00+00:00');
        $dateTimeFormatterExtensionStub = $this->createStub(DateTimeFormatterExtension::class);
        $dateTimeFormatterExtensionStub->method('formatDate')->willReturn('10. 9. 2026');
        $orderMail = $this->createOrderMail($dateTimeFormatterExtensionStub);

        $orderStub = $this->createStub(Order::class);
        $orderStub->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);
        $orderStub->method('getEmail')->willReturn('customer@example.com');
        $orderStub->method('getExpectedDeliveryDate')->willReturn($expectedDeliveryDate);
        $orderStub->method('getItems')->willReturn([]);

        $mailTemplate = $this->createMailTemplate(OrderMail::VARIABLE_EXPECTED_DELIVERY_DATE);

        $messageData = $orderMail->createMessage($mailTemplate, $orderStub);
        $expectedDeliveryDateReplacement = $messageData
            ->variablesReplacementsForBody[OrderMail::VARIABLE_EXPECTED_DELIVERY_DATE];

        $this->assertInstanceOf(Closure::class, $expectedDeliveryDateReplacement);
        $this->assertSame('10. 9. 2026', $expectedDeliveryDateReplacement());
    }

    private function createOrderMail(?DateTimeFormatterExtension $dateTimeFormatterExtension = null): OrderMail
    {
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
            $this->domain,
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
