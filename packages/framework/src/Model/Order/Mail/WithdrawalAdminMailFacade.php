<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Twig\Environment;

class WithdrawalAdminMailFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Twig\Environment $twig
     */
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly Setting $setting,
        protected readonly Environment $twig,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function sendEmail(Order $order): void
    {
        $domainId = $order->getDomainId();
        $adminEmail = $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId);
        $adminName = $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId);

        $messageData = new MessageData(
            $adminEmail,
            null,
            $this->getMailBody($order),
            $this->getMailSubject($order),
            $adminEmail,
            $adminName,
        );

        $this->mailer->sendForDomain($messageData, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getMailBody(Order $order): string
    {
        $withdrawalRequest = $order->getWithdrawalRequestThrowExceptionWhenNull();

        return $this->twig->render('@ShopsysFramework/Mail/Order/withdrawalAdminMail.html.twig', [
            'order' => $order,
            'withdrawalRequest' => $withdrawalRequest,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getMailSubject(Order $order): string
    {
        return t('New withdrawal request for order {orderNumber}', [
            '{orderNumber}' => $order->getNumber(),
        ]);
    }
}
