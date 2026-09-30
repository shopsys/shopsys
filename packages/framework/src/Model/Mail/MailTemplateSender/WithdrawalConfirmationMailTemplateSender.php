<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalConfirmationMail;
use Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalConfirmationMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFactory;

class WithdrawalConfirmationMailTemplateSender implements MailTemplateSenderInterface
{
    protected const string WITHDRAWAL_CONFIRMATION_DUMMY_HASH = 'dummy-hash';

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly WithdrawalConfirmationMailFacade $withdrawalConfirmationMailFacade,
        protected readonly WithdrawalRequestFactory $withdrawalRequestFactory,
        protected readonly WithdrawalRequestDataFactory $withdrawalRequestDataFactory,
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
        return $mailTemplate->getName() === WithdrawalConfirmationMail::ORDER_WITHDRAWAL_CONFIRMATION_NAME;
    }

    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $order = $this->orderFacade->getById($entityId);
        $withdrawalRequestData = $this->withdrawalRequestDataFactory->createFromWithdrawalRequestOrPrefilledFromOrder($order, null);
        $withdrawalRequestData->confirmationHash = static::WITHDRAWAL_CONFIRMATION_DUMMY_HASH;

        $withdrawalRequest = $this->withdrawalRequestFactory->create($order, $withdrawalRequestData);

        $this->withdrawalConfirmationMailFacade->sendMailTemplate($mailTemplate, $withdrawalRequest, $mailTo);
    }
}
