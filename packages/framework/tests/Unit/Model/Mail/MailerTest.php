<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Mail;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopsys\FrameworkBundle\Model\Mail\Email;
use Shopsys\FrameworkBundle\Model\Mail\MailEmbedCollector;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Symfony\Component\Mailer\MailerInterface;

class MailerTest extends TestCase
{
    public function testTextAlternativeContainsContentWithoutStyles(): void
    {
        $html = '<head><style>body { background:red; }</style><title>Template</title></head>'
            . '<body><style>.button { color:blue; }</style><p>Order {number} &amp; delivery</p></body>';
        $embedCollector = $this->createStub(MailEmbedCollector::class);
        $embedCollector->method('setEmbedsToMail')->willReturnArgument(0);
        $transport = $this->createMock(MailerInterface::class);
        // The transport is the external boundary; inspect the actual message handed to it.
        $transport->expects($this->once())->method('send')->with($this->callback(function (Email $message) use ($html): bool {
            $this->assertSame('Order 123 & delivery', $message->getTextBody());
            $this->assertSame(str_replace('{number}', '123', $html), $message->getHtmlBody());

            return true;
        }));
        $mailer = new Mailer($transport, $this->createStub(MailTemplateFacade::class), new NullLogger(), $embedCollector);
        $data = new MessageData('test@example.com', null, $html, 'Confirmation', 'shop@example.com', 'Shop', ['{number}' => '123']);

        $mailer->sendForDomain($data, 1);
    }
}
