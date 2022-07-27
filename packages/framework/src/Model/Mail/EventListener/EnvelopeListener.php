<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\EventListener;

use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;

class EnvelopeListener implements EventSubscriberInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider
     */
    protected MailerSettingProvider $mailerSettingProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider $mailerSettingProvider
     */
    public function __construct(MailerSettingProvider $mailerSettingProvider)
    {
        $this->mailerSettingProvider = $mailerSettingProvider;
    }

    /**
     * @param \Symfony\Component\Mailer\Event\MessageEvent $event
     */
    public function onMessage(MessageEvent $event): void
    {
        if ($this->mailerSettingProvider->isMailerMasterEmailSet() === false) {
            return;
        }

        $message = $event->getMessage();
        if (!($message instanceof Message)) {
            return;
        }

        $originalRecipients = [
            ...$this->getAddressesFromMessageHeader($message, 'To'),
            ...$this->getAddressesFromMessageHeader($message, 'Cc'),
            ...$this->getAddressesFromMessageHeader($message, 'Bcc'),
        ];
        $allowedRecipients = $this->getAllowedRecipients($originalRecipients);

        $event->getEnvelope()->setRecipients($allowedRecipients);
    }

    /**
     * @param \Symfony\Component\Mime\Message $message
     * @param string $headerName
     * @return \Symfony\Component\Mime\Address[]
     */
    protected function getAddressesFromMessageHeader(Message $message, string $headerName): array
    {
        $header = $message->getHeaders()->get($headerName);
        if ($header instanceof MailboxListHeader) {
            return $header->getAddresses();
        }

        return [];
    }

    /**
     * @param \Symfony\Component\Mime\Address[] $originalRecipients
     * @return \Symfony\Component\Mime\Address[]
     */
    protected function getAllowedRecipients(array $originalRecipients): array
    {
        $allowedRecipients = [new Address($this->mailerSettingProvider->getMailerMasterEmailAddress())];
        foreach ($originalRecipients as $originalRecipient) {
            foreach ($this->mailerSettingProvider->getMailerWhitelistExpressions() as $whitelistedPattern) {
                if (preg_match($whitelistedPattern, $originalRecipient->getAddress())) {
                    $allowedRecipients[] = $originalRecipient;
                }
            }
        }

        return $allowedRecipients;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // needs to be run after Symfony\Component\Mailer\EventListener\EnvelopeListener
            MessageEvent::class => ['onMessage', -300],
        ];
    }
}
