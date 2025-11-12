<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalCustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalMail;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;

class WithdrawalMailTemplateSender implements MailTemplateSenderInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalCustomerMailFacade $withdrawalCustomerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade $withdrawalRequestFacade
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly WithdrawalCustomerMailFacade $withdrawalCustomerMailFacade,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
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
        $withdrawalRequest = $this->withdrawalRequestFacade->getByOrder($order);

        $this->withdrawalCustomerMailFacade->sendMailTemplate($mailTemplate, $withdrawalRequest, $mailTo);
    }
}
