<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;

class OrderMailTemplateSender implements MailTemplateSenderInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderMailFacade $orderMailFacade,
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
        return str_contains($mailTemplate->getName(), OrderMail::MAIL_TEMPLATE_NAME_PREFIX);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param string $mailTo
     * @param int|null $entityId
     */
    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $order = $this->orderFacade->getById($entityId);
        $this->orderMailFacade->sendMailTemplate($mailTemplate, $order, $mailTo);
    }
}
