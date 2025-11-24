<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;

class WithdrawalMailFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     */
    public function __construct(
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function sendMail(Order $order): void
    {
        $mailTemplate = $this->orderMailFacade->getMailTemplateByStatusAndDomainId(
            $this->orderStatusFacade->getByType(OrderStatusTypeEnum::TYPE_WITHDRAWN),
            $order->getDomainId(),
        );

        $this->orderMailFacade->sendMailTemplate($mailTemplate, $order);
    }
}
