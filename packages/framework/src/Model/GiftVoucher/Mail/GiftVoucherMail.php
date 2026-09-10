<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class GiftVoucherMail
{
    public const string GIFT_VOUCHER_MAIL_TEMPLATE_NAME = 'gift_voucher';

    public function __construct(
        protected readonly MailSettingFacade $mailSettingFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $attachments
     * @param \Shopsys\FrameworkBundle\Model\Mail\GeneratedMailAttachment[] $generatedAttachments
     */
    public function createMessage(
        MailTemplate $mailTemplate,
        Order $order,
        array $attachments,
        array $generatedAttachments,
    ): MessageData {
        $domainId = $order->getDomainId();

        return new MessageData(
            $order->getEmail(),
            $mailTemplate->getBccEmail(),
            $mailTemplate->getBody(),
            $mailTemplate->getSubject(),
            $this->mailSettingFacade->getMainAdminMail($domainId),
            $this->mailSettingFacade->getMainAdminMailName($domainId),
            attachments: $attachments,
            generatedAttachments: $generatedAttachments,
        );
    }
}
