<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Mail;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Mail\EventListener\EnvelopeListener;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;

class EnvelopeListenerTest extends TestCase
{
    /**
     * @dataProvider onMessageDataProvider
     * @param \Symfony\Component\Mime\Address|null $masterMail
     * @param string $deliveryWhitelist
     * @param \Symfony\Component\Mime\Address[] $mailsTo
     * @param \Symfony\Component\Mime\Address|null $mailCc
     * @param \Symfony\Component\Mime\Address|null $mailBcc
     * @param array $expectedRecipients
     */
    public function testOnMessage(
        ?Address $masterMail,
        string $deliveryWhitelist,
        array $mailsTo,
        ?Address $mailCc,
        ?Address $mailBcc,
        array $expectedRecipients
    ): void {
        $envelopeListener = new EnvelopeListener($masterMail !== null ? $masterMail->getAddress() : '', $deliveryWhitelist);
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
        ?Address $mailBcc
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

        return new MessageEvent(new Message($headers), $envelope, 'transport');
    }

    /**
     * @return iterable
     */
    public function onMessageDataProvider(): iterable
    {
        $shopsysMasterMail = new Address('no-reply-mastermail@shopsys.com');
        $netdeveloNoReplyMail = new Address('no-reply@netdevelo.cz');
        $shopsysNoReplyMail1 = new Address('no-reply@shopsys.com');
        $shopsysNoReplyMail2 = new Address('no-reply2@shopsys.com');
        $shopsysNoReplyMail3 = new Address('no-reply3@shopsys.com');

        // when no master mail is set, whitelist is ignored
        yield [
            'masterMail' => null,
            'deliveryWhitelist' => '/@shopsys\.com$/',
            'mailsTo' => [$netdeveloNoReplyMail],
            'mailCc' => null,
            'mailBcc' => null,
            'expectedRecipients' => [
                $netdeveloNoReplyMail,
            ],
        ];
        // when master mail is set without whitelist, all mails are delivered to the master mail only
        yield [
            'masterMail' => $shopsysMasterMail,
            'deliveryWhitelist' => '',
            'mailsTo' => [$shopsysNoReplyMail1],
            'mailCc' => $shopsysNoReplyMail2,
            'mailBcc' => $shopsysNoReplyMail3,
            'expectedRecipients' => [
                $shopsysMasterMail,
            ],
        ];
        // when master mail is set with whitelist, all mails are delivered to the master mail and to the recipients that match the whitelisted patterns only
        yield [
            'masterMail' => $shopsysMasterMail,
            'deliveryWhitelist' => '/@shopsys\.com$/',
            'mailsTo' => [$netdeveloNoReplyMail],
            'mailCc' => $shopsysNoReplyMail1,
            'mailBcc' => $shopsysNoReplyMail2,
            'expectedRecipients' => [
                $shopsysMasterMail,
                $shopsysNoReplyMail1,
                $shopsysNoReplyMail2,
            ],
        ];
        // when there are multiple patterns in the whitelist, mails are delivered to the addresses that match at least one of the patterns
        yield [
            'masterMail' => $shopsysMasterMail,
            'deliveryWhitelist' => '/@shopsys\.com$/,/@netdevelo\.cz$/',
            'mailsTo' => [$shopsysNoReplyMail1],
            'mailCc' => $shopsysNoReplyMail2,
            'mailBcc' => $netdeveloNoReplyMail,
            'expectedRecipients' => [
                $shopsysMasterMail,
                $shopsysNoReplyMail1,
                $shopsysNoReplyMail2,
                $netdeveloNoReplyMail,
            ],
        ];
        // when there is no master mail nor whitelist, all mails are delivered to the required addresses without restrictions
        yield [
            'masterMail' => null,
            'deliveryWhitelist' => '',
            'mailsTo' => [$netdeveloNoReplyMail],
            'mailCc' => $shopsysNoReplyMail2,
            'mailBcc' => $shopsysNoReplyMail3,
            'expectedRecipients' => [
                $netdeveloNoReplyMail,
                $shopsysNoReplyMail2,
                $shopsysNoReplyMail3,
            ],
        ];
    }
}
