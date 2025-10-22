<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalMail;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;

class WithdrawalMailTemplateSender implements MailTemplateSenderInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalMailFacade $withdrawalMailFacade
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly WithdrawalMailFacade $withdrawalMailFacade,
    ) {
    }

    /**
     * @return string
     */
    #[Override]
    public function getFormLabelForEntityIdentifier(): string
    {
        return t('Order ID');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @return bool
     */
    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return $mailTemplate->getName() === WithdrawalMail::MAIL_TEMPLATE_NAME;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param string $mailTo
     * @param int|null $entityId
     */
    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        if ($entityId === null) {
            throw new InvalidArgumentException('Order ID is required for withdrawal mail template.');
        }

        $order = $this->orderFacade->getById($entityId);
        $this->withdrawalMailFacade->sendMailTemplate($mailTemplate, $order, $mailTo);
    }
}
