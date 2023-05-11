<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\EventListener;

use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Model\Mail\Email;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;

class EnvelopeListener implements EventSubscriberInterface
{
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
        $message = $event->getMessage();

        if (!($message instanceof Message)) {
            return;
        }

        $originalRecipients = [
            ...$this->getAddressesFromMessageHeader($message, 'To'),
            ...$this->getAddressesFromMessageHeader($message, 'Cc'),
            ...$this->getAddressesFromMessageHeader($message, 'Bcc'),
        ];

        if ($message instanceof Email && $message->getDomainId() !== 0) {
            $allowedRecipients = $this->getAllowedRecipientsOnDomain($originalRecipients, $message->getDomainId());
            $isWhitelistEnabled = $this->mailerSettingProvider->isWhitelistEnabled($message->getDomainId());
        } else {
            DeprecationHelper::trigger('Email is not instance of ' . Email::class . ' this will throw exception in next major version.');

            $allowedRecipients = $this->getAllowedRecipients($originalRecipients);
            $isWhitelistEnabled = false;
        }

        if (!$isWhitelistEnabled && !$this->mailerSettingProvider->isMailerMasterEmailSet()) {
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
     * @return \Symfony\Component\Mime\Address[]
     * @deprecated use getAllowedRecipientsOnDomain() instead
     */
    protected function getAllowedRecipients(array $originalRecipients): array
    {
        DeprecationHelper::triggerMethod(__METHOD__, 'getAllowedRecipientsOnDomain()');

        $allowedRecipients = [];

        if ($this->mailerSettingProvider->isMailerMasterEmailSet()) {
            $allowedRecipients = [new Address($this->mailerSettingProvider->getMailerMasterEmailAddress())];
        }

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
