<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ComplaintMail implements MessageFactoryInterface
{
    public const string MAIL_TEMPLATE_NAME_PREFIX = 'complaint_status_';
    public const string VARIABLE_COMPLAINT_NUMBER = '{complaint_number}';
    public const string VARIABLE_ORDER_NUMBER = '{order_number}';
    public const string VARIABLE_DATE = '{date}';
    public const string VARIABLE_URL = '{url}';
    public const string VARIABLE_COMPLAINT_DETAIL_URL = '{complaint_detail_url}';
    public const string VARIABLE_COMPLAINT_RESOLUTION = '{complaint_resolution}';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly Domain $domain,
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        protected readonly ComplaintResolutionEnum $complaintResolutionEnum,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     */
    #[Override]
    public function createMessage(MailTemplate $mailTemplate, $complaint): MessageData
    {
        $complaintDomainId = $complaint->getDomainId();

        return new MessageData(
            $complaint->getEmail(),
            $mailTemplate->getBccEmail(),
            $mailTemplate->getBody(),
            $mailTemplate->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $complaintDomainId),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $complaintDomainId),
            $this->getVariablesReplacementsForBody($complaint),
            $this->getVariablesReplacementsForSubject($complaint),
        );
    }

    public function getMailTemplateNameByStatus(ComplaintStatus $complaintStatus): string
    {
        return static::MAIL_TEMPLATE_NAME_PREFIX . $complaintStatus->getId();
    }

    protected function getVariablesReplacementsForBody(Complaint $complaint): array
    {
        $complaintDomainId = $complaint->getDomainId();

        $router = $this->domainRouterFactory->getRouter($complaintDomainId);

        return [
            self::VARIABLE_COMPLAINT_NUMBER => htmlspecialchars($complaint->getNumber(), ENT_QUOTES),
            self::VARIABLE_COMPLAINT_DETAIL_URL => $this->getComplaintDetailUrl($complaint),
            self::VARIABLE_ORDER_NUMBER => htmlspecialchars($complaint->getOrderNumberOrManualDocumentNumber(), ENT_QUOTES),
            self::VARIABLE_DATE => $this->getFormattedDateTime($complaint),
            self::VARIABLE_URL => $router->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            self::VARIABLE_COMPLAINT_RESOLUTION => array_search($complaint->getResolution(), $this->complaintResolutionEnum->getAllIndexedByTranslationsForCustomer(), true),
        ];
    }

    protected function getVariablesReplacementsForSubject(Complaint $complaint): array
    {
        return [
            self::VARIABLE_COMPLAINT_NUMBER => $complaint->getNumber(),
            self::VARIABLE_ORDER_NUMBER => $complaint->getOrderNumberOrManualDocumentNumber(),
            self::VARIABLE_DATE => $this->getFormattedDateTime($complaint),
        ];
    }

    protected function getFormattedDateTime(Complaint $complaint): string
    {
        return $this->dateTimeFormatterExtension->formatDateTime(
            $complaint->getCreatedAt(),
            $this->getDomainLocaleByComplaint($complaint),
        );
    }

    protected function getDomainLocaleByComplaint(Complaint $complaint): string
    {
        return $this->domain->getDomainConfigById($complaint->getDomainId())->getLocale();
    }

    protected function getComplaintDetailUrl(Complaint $complaint): string
    {
        return $this->domainRouterFactory->getRouter($complaint->getDomainId())->generate(
            'front_complaint_detail',
            ['complaintNumber' => $complaint->getNumber()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
