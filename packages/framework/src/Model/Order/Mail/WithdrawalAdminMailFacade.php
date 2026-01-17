<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Twig\Environment;

class WithdrawalAdminMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly Setting $setting,
        protected readonly Environment $twig,
        protected readonly Domain $domain,
    ) {
    }

    public function sendEmail(WithdrawalRequest $withdrawalRequest): void
    {
        $order = $withdrawalRequest->getOrder();
        $domainId = $order->getDomainId();
        $adminEmail = $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId);
        $adminName = $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId);

        $messageData = new MessageData(
            $adminEmail,
            null,
            $this->getMailBody($withdrawalRequest),
            $this->getMailSubject($withdrawalRequest),
            $adminEmail,
            $adminName,
        );

        $this->mailer->sendForDomain($messageData, $domainId);
    }

    protected function getMailBody(WithdrawalRequest $withdrawalRequest): string
    {
        return $this->twig->render('@ShopsysFramework/Mail/Order/withdrawalAdminMail.html.twig', [
            'order' => $withdrawalRequest->getOrder(),
            'withdrawalRequest' => $withdrawalRequest,
        ]);
    }

    protected function getMailSubject(WithdrawalRequest $withdrawalRequest): string
    {
        $order = $withdrawalRequest->getOrder();

        return t('New withdrawal request for order {orderNumber}', [
            '{orderNumber}' => $order->getNumber(),
        ], locale: $this->domain->getDomainConfigById($order->getDomainId())->getLocale());
    }
}
