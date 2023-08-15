<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Mail;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Mail\Email;
use Shopsys\FrameworkBundle\Model\Mail\EventListener\EnvelopeListener;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\MailboxListHeader;

class EnvelopeListenerTest extends TestCase
{
    /**
     * @dataProvider onMessageDataProvider
     * @param string|null $deliveryWhitelist
     * @param bool $isWhitelistEnabled
     * @param bool $isWhitelistForced
     * @param \Symfony\Component\Mime\Address[] $mailsTo
     * @param \Symfony\Component\Mime\Address|null $mailCc
     * @param \Symfony\Component\Mime\Address|null $mailBcc
     * @param array $expectedRecipients
     */
    public function testOnMessage(
        ?string $deliveryWhitelist,
        bool $isWhitelistEnabled,
        bool $isWhitelistForced,
        array $mailsTo,
        ?Address $mailCc,
        ?Address $mailBcc,
        array $expectedRecipients,
    ): void {
        $mailSettingFacadeMock = $this->getMockBuilder(MailSettingFacade::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mailSettingFacadeMock->method('getMailWhitelist')->willReturn($deliveryWhitelist);
        $mailSettingFacadeMock->method('isWhitelistEnabled')->willReturn($isWhitelistEnabled);

        $mailerSettingProvider = new MailerSettingProvider(
            'dsn',
            $isWhitelistForced,
            $mailSettingFacadeMock,
        );
        $envelopeListener = new EnvelopeListener($mailerSettingProvider);
        $messageEvent = $this->getMessageEvent($mailsTo, $mailCc, $mailBcc);

        $envelopeListener->onMessage($messageEvent);
        $this->assertEquals($expectedRecipients, $messageEvent->getEnvelope()->getRecipients());
    }

    /**
     * @param \Symfony\Component\Mime\Address[] $mailsTo
     * @param \Symfony\Component\Mime\Address|null $mailCc
     * @param \Symfony\Component\Mime\Address|null $mailBcc
     * @return \Symfony\Component\Mailer\Event\MessageEvent
     */
    protected function getMessageEvent(
        array $mailsTo,
        ?Address $mailCc,
        ?Address $mailBcc,
    ): MessageEvent {
        $sender = new Address('no-reply@shopsys.com');
        $headers = new Headers(new MailboxListHeader('To', $mailsTo));
        $recipients = $mailsTo;

        if ($mailCc !== null) {
            $headers->add(new MailboxListHeader('Cc', [$mailCc]));
            $recipients[] = $mailCc;
        }

        if ($mailBcc !== null) {
            $headers->add(new MailboxListHeader('Bcc', [$mailBcc]));
            $recipients[] = $mailBcc;
        }
        $envelope = new Envelope($sender, $recipients);

        return new MessageEvent(new Email(1, $headers), $envelope, 'transport');
    }

    /**
     * @return iterable
     */
    public function onMessageDataProvider(): iterable
    {
        $netdeveloNoReplyMail = new Address('no-reply@netdevelo.cz');
        $shopsysNoReplyMail1 = new Address('no-reply@shopsys.com');
        $shopsysNoReplyMail2 = new Address('no-reply2@shopsys.com');
        $shopsysNoReplyMail3 = new Address('no-reply3@shopsys.com');
        $nonExistingMail = new Address('no-reply@domain.tld');

        // when whitelist is set but not enabled, all mails are delivered to the required addresses without restrictions
        yield [
            'deliveryWhitelist' => json_encode(['/@shopsys\.com$/'], JSON_THROW_ON_ERROR),
            'isWhitelistEnabled' => false,
            'isWhitelistForced' => false,
            'mailsTo' => [$netdeveloNoReplyMail],
            'mailCc' => null,
            'mailBcc' => null,
            'expectedRecipients' => [
                $netdeveloNoReplyMail,
            ],
        ];

        // when whitelist is enabled but empty, all mails are sent to the non-existing address
        yield [
            'deliveryWhitelist' => null,
            'isWhitelistEnabled' => true,
            'isWhitelistForced' => false,
            'mailsTo' => [$shopsysNoReplyMail1],
            'mailCc' => $shopsysNoReplyMail2,
            'mailBcc' => $shopsysNoReplyMail3,
            'expectedRecipients' => [
                $nonExistingMail,
            ],
        ];

        // when whitelist is set and enabled, all mails are delivered to the recipients that match the whitelisted patterns only
        yield [
            'deliveryWhitelist' => json_encode(['/@shopsys\.com$/'], JSON_THROW_ON_ERROR),
            'isWhitelistEnabled' => true,
            'isWhitelistForced' => false,
            'mailsTo' => [$netdeveloNoReplyMail],
            'mailCc' => $shopsysNoReplyMail1,
            'mailBcc' => $shopsysNoReplyMail2,
            'expectedRecipients' => [
                $shopsysNoReplyMail1,
                $shopsysNoReplyMail2,
            ],
        ];

        // when whitelist is set disabled but forced, all mails are still delivered to the recipients that match the whitelisted patterns only
        yield [
            'deliveryWhitelist' => json_encode(['/@shopsys\.com$/'], JSON_THROW_ON_ERROR),
            'isWhitelistEnabled' => false,
            'isWhitelistForced' => true,
            'mailsTo' => [$netdeveloNoReplyMail],
            'mailCc' => $shopsysNoReplyMail1,
            'mailBcc' => $shopsysNoReplyMail2,
            'expectedRecipients' => [
                $shopsysNoReplyMail1,
                $shopsysNoReplyMail2,
            ],
        ];

        // when there are multiple patterns in the whitelist, mails are delivered to the addresses that match at least one of the patterns
        yield [
            'deliveryWhitelist' => json_encode(['/@shopsys\.com$/', '/@netdevelo\.cz$/'], JSON_THROW_ON_ERROR),
            'isWhitelistEnabled' => true,
            'isWhitelistForced' => false,
            'mailsTo' => [$shopsysNoReplyMail1],
            'mailCc' => $shopsysNoReplyMail2,
            'mailBcc' => $netdeveloNoReplyMail,
            'expectedRecipients' => [
                $shopsysNoReplyMail1,
                $shopsysNoReplyMail2,
                $netdeveloNoReplyMail,
            ],
        ];
    }
}
