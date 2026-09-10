<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Mail\GiftVoucherMail;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Mail\GiftVoucherMailFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;

class GiftVoucherMailTemplateSender implements MailTemplateSenderInterface
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly GiftVoucherMailFacade $giftVoucherMailFacade,
    ) {
    }

    #[Override]
    public function getFormLabelForEntityIdentifier(): string
    {
        return t('Order ID');
    }

    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return $mailTemplate->getName() === GiftVoucherMail::GIFT_VOUCHER_MAIL_TEMPLATE_NAME;
    }

    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $order = $this->orderFacade->getById($entityId);
        $this->giftVoucherMailFacade->sendMailTemplate($mailTemplate, $order, $mailTo);
    }
}
