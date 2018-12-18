<?php

namespace Tests\ShopBundle\Functional\Model\Order\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
use Symfony\Component\Routing\RouterInterface;
use Tests\ShopBundle\Test\FunctionalTestCase;
use Twig_Environment;

class OrderMailTest extends FunctionalTestCase
{
    public function testGetMailTemplateNameByStatus()
    {
        $orderStatus1 = $this->getMockBuilder(OrderStatus::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatus1->expects($this->atLeastOnce())->method('getId')->willReturn(1);

        $orderStatus2 = $this->getMockBuilder(OrderStatus::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatus2->expects($this->atLeastOnce())->method('getId')->willReturn(2);

        $mailTempleteName1 = OrderMail::getMailTemplateNameByStatus($orderStatus1);
        $mailTempleteName2 = OrderMail::getMailTemplateNameByStatus($orderStatus2);

        $this->assertNotEmpty($mailTempleteName1);
        $this->assertInternalType('string', $mailTempleteName1);

        $this->assertNotEmpty($mailTempleteName2);
        $this->assertInternalType('string', $mailTempleteName2);

        $this->assertNotSame($mailTempleteName1, $mailTempleteName2);
    }

    public function testGetMessageByOrder()
    {
        $routerMock = $this->getMockBuilder(RouterInterface::class)->setMethods(['generate'])->getMockForAbstractClass();
        $routerMock->expects($this->any())->method('generate')->willReturn('generatedUrl');

        $domainRouterFactoryMock = $this->getMockBuilder(DomainRouterFactory::class)
            ->setMethods(['getRouter'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainRouterFactoryMock->expects($this->any())->method('getRouter')->willReturn($routerMock);

        $twigMock = $this->getMockBuilder(Twig_Environment::class)->disableOriginalConstructor()->getMock();
        $orderItemPriceCalculationMock = $this->getMockBuilder(OrderItemPriceCalculation::class)->disableOriginalConstructor()->getMock();
        $settingMock = $this->getMockBuilder(Setting::class)->disableOriginalConstructor()->getMock();
        $priceExtensionMock = $this->getMockBuilder(PriceExtension::class)->disableOriginalConstructor()->getMock();
        $dateTimeFormatterExtensionMock = $this->getMockBuilder(DateTimeFormatterExtension::class)->disableOriginalConstructor()->getMock();
        $orderUrlGeneratorMock = $this->getMockBuilder(OrderUrlGenerator::class)->disableOriginalConstructor()->getMock();

        $domainConfig = new DomainConfig(1, 'http://example.com:8080', 'example', 'cs');
        $domain = new Domain([$domainConfig], $settingMock);

        $orderMail = new OrderMail(
            $settingMock,
            $domainRouterFactoryMock,
            $twigMock,
            $orderItemPriceCalculationMock,
            $domain,
            $priceExtensionMock,
            $dateTimeFormatterExtensionMock,
            $orderUrlGeneratorMock
        );

        $order = $this->getReference('order_1');

        $mailTemplateData = new MailTemplateData();
        $mailTemplateData->subject = 'subject';
        $mailTemplateData->body = 'body';
        $mailTemplate = new MailTemplate('templateName', 1, $mailTemplateData);

        $messageData = $orderMail->createMessage($mailTemplate, $order);

        $this->assertInstanceOf(MessageData::class, $messageData);
        $this->assertSame($mailTemplate->getSubject(), $messageData->subject);
        $this->assertSame($mailTemplate->getBody(), $messageData->body);
    }
}
