<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Mail;

use App\Model\Mail\MailTemplate;
use App\Model\Mail\MailTemplateData;
use App\Model\Order\Mail\OrderMail;
use App\Model\Order\Order;
use App\Model\Order\Status\OrderStatus;
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
        $routerStub = $this->createStub(DomainRouter::class);
        $routerStub->method('generate')->willReturn('generatedUrl');

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);
        $domainRouterFactoryStub->method('getRouter')->willReturn($routerStub);

        $twigStub = $this->createStub(Environment::class);
        $settingStub = $this->createStub(Setting::class);
        $settingStub->method('getForDomain')->willReturn('no-reply@shopsys.com');
        $priceExtensionStub = $this->createStub(PriceExtension::class);
        $dateTimeFormatterExtensionStub = $this->createStub(DateTimeFormatterExtension::class);
        $orderUrlGeneratorStub = $this->createStub(OrderUrlGenerator::class);
        $hiddenPriceExtensionStub = $this->createStub(HiddenPriceExtension::class);
        $paymentInstructionFacadeStub = $this->createStub(PaymentInstructionFacade::class);
        $mailDisplayPriceResolverStub = $this->createStub(MailDisplayPriceResolver::class);
        $withdrawalRequestFacadeStub = $this->createStub(WithdrawalRequestFacade::class);

        $orderMail = new OrderMail(
            $settingStub,
            $domainRouterFactoryStub,
            $twigStub,
            $this->orderItemPriceCalculation,
            $this->domain,
            $priceExtensionStub,
            $dateTimeFormatterExtensionStub,
            $orderUrlGeneratorStub,
            $hiddenPriceExtensionStub,
            $paymentInstructionFacadeStub,
            $mailDisplayPriceResolverStub,
            $withdrawalRequestFacadeStub,
        );

        $order = $this->getReference('order_1', Order::class);

        $mailTemplateData = new MailTemplateData();
        $mailTemplateData->subject = 'subject';
        $mailTemplateData->body = 'body';
        $mailTemplate = new MailTemplate('templateName', Domain::FIRST_DOMAIN_ID, $mailTemplateData);

        $messageData = $orderMail->createMessage($mailTemplate, $order);

        $this->assertInstanceOf(MessageData::class, $messageData);
        $this->assertSame($mailTemplate->getSubject(), $messageData->subject);
        $this->assertSame($mailTemplate->getBody(), $messageData->body);
    }
}
