<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WithdrawalConfirmationMail implements MessageFactoryInterface
{
    public const string ORDER_WITHDRAWAL_CONFIRMATION_NAME = 'order_withdrawal_confirmation';
    public const string VARIABLE_URL = '{url}';
    public const string VARIABLE_ORDER_NUMBER = '{order_number}';
    public const string VARIABLE_DOMAIN = '{domain}';

    public function __construct(
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest $withdrawalRequest
     */
    #[Override]
    public function createMessage(MailTemplate $template, $withdrawalRequest): MessageData
    {
        $domainId = $withdrawalRequest->getOrder()->getDomainId();

        return new MessageData(
            $withdrawalRequest->getOrder()->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId),
            $this->getBodyValuesIndexedByVariableName($withdrawalRequest, $domainId),
            $this->getSubjectValuesIndexedByVariableName($withdrawalRequest),
        );
    }

    /**
     * @return array<string, \Closure>
     */
    protected function getBodyValuesIndexedByVariableName(
        WithdrawalRequest $withdrawalRequest,
        int $domainId,
    ): array {
        return [
            self::VARIABLE_URL => fn () => $this->getWithdrawalConfirmationUrl($withdrawalRequest->getConfirmationHash(), $domainId),
            self::VARIABLE_ORDER_NUMBER => fn () => htmlspecialchars($withdrawalRequest->getOrder()->getNumber(), ENT_QUOTES),
            self::VARIABLE_DOMAIN => fn () => htmlspecialchars($this->domain->getDomainConfigById($domainId)->getName(), ENT_QUOTES),
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    protected function getSubjectValuesIndexedByVariableName(WithdrawalRequest $withdrawalRequest): array
    {
        return [
            self::VARIABLE_ORDER_NUMBER => fn () => $withdrawalRequest->getOrder()->getNumber(),
            self::VARIABLE_DOMAIN => fn () => $this->domain->getDomainConfigById($withdrawalRequest->getOrder()->getDomainId())->getName(),
        ];
    }

    protected function getWithdrawalConfirmationUrl(string $hash, int $domainId): string
    {
        $router = $this->domainRouterFactory->getRouter($domainId);

        return $router->generate(
            'front_order_withdrawal_confirmation',
            ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
