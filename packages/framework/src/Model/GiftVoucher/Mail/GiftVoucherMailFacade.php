<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherPdfGenerator;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherRepository;
use Shopsys\FrameworkBundle\Model\Mail\GeneratedMailAttachment;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class GiftVoucherMailFacade
{
    public const string GIFT_VOUCHER_IDS_EMAIL_METADATA_KEY = 'giftVoucherIds';

    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly GiftVoucherMail $giftVoucherMail,
        protected readonly GiftVoucherPdfGenerator $giftVoucherPdfGenerator,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly GiftVoucherRepository $giftVoucherRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[] $giftVouchers
     */
    public function sendGiftVouchersMail(Order $order, array $giftVouchers): void
    {
        if ($giftVouchers === []) {
            return;
        }

        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
            GiftVoucherMail::GIFT_VOUCHER_MAIL_TEMPLATE_NAME,
            $order->getDomainId(),
        );

        if (!$mailTemplate->isSendMail()) {
            return;
        }

        $this->sendMessage($mailTemplate, $order, $giftVouchers);
    }

    public function sendMailTemplate(MailTemplate $mailTemplate, Order $order, ?string $forceSendTo = null): void
    {
        $giftVouchers = $this->giftVoucherRepository->getAllCreatedOnOrder($order);

        $this->sendMessage($mailTemplate, $order, $giftVouchers, $forceSendTo);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[] $giftVouchers
     */
    protected function sendMessage(
        MailTemplate $mailTemplate,
        Order $order,
        array $giftVouchers,
        ?string $forceSendTo = null,
    ): void {
        $generatedAttachments = [];

        foreach ($giftVouchers as $giftVoucher) {
            $generatedAttachments[] = $this->createGiftVoucherAttachment($giftVoucher);
        }

        $messageData = $this->giftVoucherMail->createMessage(
            $mailTemplate,
            $order,
            $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate),
            $generatedAttachments,
        );
        $messageData->metadata[self::GIFT_VOUCHER_IDS_EMAIL_METADATA_KEY] = array_map(
            static fn (GiftVoucher $giftVoucher) => $giftVoucher->getId(),
            $giftVouchers,
        );

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $this->mailer->sendForDomain($messageData, $order->getDomainId());
    }

    protected function createGiftVoucherAttachment(GiftVoucher $giftVoucher): GeneratedMailAttachment
    {
        return new GeneratedMailAttachment(
            $this->giftVoucherPdfGenerator->generatePdfContent($giftVoucher),
            sprintf('gift-voucher-%s.pdf', $giftVoucher->getCode()),
            'application/pdf',
        );
    }
}
