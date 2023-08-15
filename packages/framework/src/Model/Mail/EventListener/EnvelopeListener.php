<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\EventListener;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Mail\Email;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;

class EnvelopeListener implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider $mailerSettingProvider
     */
    public function __construct(
        protected readonly MailerSettingProvider $mailerSettingProvider,
    ) {
    }

    /**
     * @param \Symfony\Component\Mailer\Event\MessageEvent $event
     */
    public function onMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if (!($message instanceof Email)) {
            throw new InvalidArgumentException(sprintf('Message must be instance of %s.', Email::class));
        }

        $originalRecipients = [
            ...$this->getAddressesFromMessageHeader($message, 'To'),
            ...$this->getAddressesFromMessageHeader($message, 'Cc'),
            ...$this->getAddressesFromMessageHeader($message, 'Bcc'),
        ];

        $allowedRecipients = $this->getAllowedRecipientsOnDomain($originalRecipients, $message->getDomainId());
        $isWhitelistEnabled = $this->mailerSettingProvider->isWhitelistEnabled($message->getDomainId());

        if (!$isWhitelistEnabled) {
            return;
        }

        if ($allowedRecipients === []) {
            // set a non-existing address because recipient list cannot be empty
            $allowedRecipients = [new Address('no-reply@domain.tld')];
        }

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
     * @param int $domainId
     * @return \Symfony\Component\Mime\Address[]
     */
    protected function getAllowedRecipientsOnDomain(array $originalRecipients, int $domainId): array
    {
        $allowedRecipients = [];

        foreach ($originalRecipients as $originalRecipient) {
            foreach ($this->mailerSettingProvider->getWhitelistPatternsAsArray($domainId) as $whitelistedPattern) {
                if (preg_match($whitelistedPattern, $originalRecipient->getAddress())) {
                    $allowedRecipients[] = $originalRecipient;
                }
            }
        }

        return $allowedRecipients;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // needs to be run after Symfony\Component\Mailer\EventListener\EnvelopeListener
            MessageEvent::class => ['onMessage', -300],
        ];
    }
}
