<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ComplaintMail implements MessageFactoryInterface
{
    protected const MAIL_TEMPLATE_NAME_PREFIX = 'complaint_status_';
    public const VARIABLE_COMPLAINT_NUMBER = '{complaint_number}';
    public const VARIABLE_ORDER_NUMBER = '{order_number}';
    public const VARIABLE_DATE = '{date}';
    public const VARIABLE_URL = '{url}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly Domain $domain,
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $mailTemplate, $complaint)
    {
        $complaintDomainId = $complaint->getDomainId();

        return new MessageData(
            $complaint->getCustomerUser() ? $complaint->getCustomerUser()->getEmail() : $complaint->getOrder()->getEmail(),
            $mailTemplate->getBccEmail(),
            $mailTemplate->getBody(),
            $mailTemplate->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $complaintDomainId),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $complaintDomainId),
            $this->getVariablesReplacementsForBody($complaint),
            $this->getVariablesReplacementsForSubject($complaint),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $complaintStatus
     * @return string
     */
    public static function getMailTemplateNameByStatus(ComplaintStatus $complaintStatus)
    {
        return static::MAIL_TEMPLATE_NAME_PREFIX . $complaintStatus->getId();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     * @return array
     */
    protected function getVariablesReplacementsForBody(Complaint $complaint)
    {
        $complaintDomainId = $complaint->getDomainId();

        $router = $this->domainRouterFactory->getRouter($complaintDomainId);

        return [
            self::VARIABLE_COMPLAINT_NUMBER => htmlspecialchars($complaint->getNumber(), ENT_QUOTES),
            self::VARIABLE_ORDER_NUMBER => htmlspecialchars($complaint->getOrder()->getNumber(), ENT_QUOTES),
            self::VARIABLE_DATE => $this->getFormattedDateTime($complaint),
            self::VARIABLE_URL => $router->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     * @return array
     */
    protected function getVariablesReplacementsForSubject(Complaint $complaint)
    {
        return [
            self::VARIABLE_COMPLAINT_NUMBER => $complaint->getNumber(),
            self::VARIABLE_ORDER_NUMBER => $complaint->getOrder()->getNumber(),
            self::VARIABLE_DATE => $this->getFormattedDateTime($complaint),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     * @return string
     */
    protected function getFormattedDateTime(Complaint $complaint)
    {
        return $this->dateTimeFormatterExtension->formatDateTime(
            $complaint->getCreatedAt(),
            $this->getDomainLocaleByComplaint($complaint),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     * @return string
     */
    protected function getDomainLocaleByComplaint(Complaint $complaint)
    {
        return $this->domain->getDomainConfigById($complaint->getDomainId())->getLocale();
    }
}
