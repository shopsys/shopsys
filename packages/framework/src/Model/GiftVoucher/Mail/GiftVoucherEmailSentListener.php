<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher\Mail;

use Override;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\Mail\Email;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Throwable;

class GiftVoucherEmailSentListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly GiftVoucherFacade $giftVoucherFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function onSentMessage(SentMessageEvent $event): void
    {
        $message = $event->getMessage()->getOriginalMessage();

        if (!$message instanceof Email) {
            return;
        }

        $giftVoucherIds = $message->getMetadata(GiftVoucherMailFacade::GIFT_VOUCHER_IDS_EMAIL_METADATA_KEY);

        if (!is_array($giftVoucherIds) || $giftVoucherIds === []) {
            return;
        }

        try {
            $this->giftVoucherFacade->markEmailAsSentByIds($giftVoucherIds);
        } catch (Throwable $exception) {
            $this->logger->error(
                'Marking gift vouchers email as sent failed',
                [
                    'giftVoucherIds' => $giftVoucherIds,
                    'exception' => $exception,
                ],
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            SentMessageEvent::class => 'onSentMessage',
        ];
    }
}
