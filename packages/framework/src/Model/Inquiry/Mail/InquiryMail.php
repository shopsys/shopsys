<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Mailer\MailerHelper;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Inquiry\Inquiry;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Product\Image\ProductImageFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InquiryMail
{
    public const string CUSTOMER_MAIL_TEMPLATE_NAME = 'product_inquiry_customer';
    public const string ADMIN_MAIL_TEMPLATE_NAME = 'product_inquiry_admin';

    public const string VARIABLE_FULL_NAME = '{fullName}';
    public const string VARIABLE_EMAIL = '{email}';
    public const string VARIABLE_TELEPHONE = '{telephone}';
    public const string VARIABLE_COMPANY_NAME = '{companyName}';
    public const string VARIABLE_COMPANY_NUMBER = '{companyNumber}';
    public const string VARIABLE_COMPANY_TAX_NUMBER = '{companyTaxNumber}';
    public const string VARIABLE_NOTE = '{note}';

    public const string VARIABLE_PRODUCT_NAME = '{productName}';
    public const string VARIABLE_PRODUCT_CATALOG_NUMBER = '{productCatnum}';
    public const string VARIABLE_PRODUCT_URL = '{productUrl}';
    public const string VARIABLE_PRODUCT_IMAGE = '{productImageUrl}';

    public const string VARIABLE_ADMIN_INQUIRY_DETAIL_URL = '{adminInquiryDetailUrl}';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly Domain $domain,
        protected readonly ProductImageFacade $productImageFacade,
        protected readonly MailerHelper $mailerHelper,
        protected readonly AdministrationRouter $administrationRouter,
    ) {
    }

    public function createMessageForAdmin(MailTemplate $template, Inquiry $inquiry): MessageData
    {
        return new MessageData(
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $inquiry->getDomainId()),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $inquiry->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $inquiry->getDomainId()),
            $this->getBodyVariablesReplacementsForAdmin($inquiry),
            $this->getSubjectVariablesReplacements($inquiry),
        );
    }

    public function createMessageForCustomer(MailTemplate $template, Inquiry $inquiry): MessageData
    {
        return new MessageData(
            $inquiry->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $inquiry->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $inquiry->getDomainId()),
            $this->getBodyVariablesReplacements($inquiry),
            $this->getSubjectVariablesReplacements($inquiry),
        );
    }

    /**
     * @return array<string, \Closure>
     */
    protected function getSubjectVariablesReplacements(Inquiry $inquiry): array
    {
        return [
            self::VARIABLE_FULL_NAME => fn () => htmlspecialchars($inquiry->getFullName(), ENT_QUOTES),
            self::VARIABLE_EMAIL => fn () => htmlspecialchars($inquiry->getEmail(), ENT_QUOTES),
            self::VARIABLE_TELEPHONE => fn () => htmlspecialchars($inquiry->getTelephone(), ENT_QUOTES),
            self::VARIABLE_COMPANY_NAME => fn () => $this->mailerHelper->escapeOptionalString($inquiry->getCompanyName()),
            self::VARIABLE_COMPANY_NUMBER => fn () => $this->mailerHelper->escapeOptionalString($inquiry->getCompanyNumber()),
            self::VARIABLE_COMPANY_TAX_NUMBER => fn () => $this->mailerHelper->escapeOptionalString($inquiry->getCompanyTaxNumber()),
            self::VARIABLE_PRODUCT_NAME => fn () => $inquiry->getProduct()?->getName(),
            self::VARIABLE_PRODUCT_CATALOG_NUMBER => fn () => htmlspecialchars($inquiry->getProductCatnum(), ENT_QUOTES),
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    protected function getBodyVariablesReplacements(Inquiry $inquiry): array
    {
        return [
            ...$this->getSubjectVariablesReplacements($inquiry),
            self::VARIABLE_NOTE => fn () => $this->mailerHelper->escapeOptionalString($inquiry->getNote()),
            self::VARIABLE_PRODUCT_URL => fn () => $this->getProductUrl($inquiry),
            self::VARIABLE_PRODUCT_IMAGE => fn () => $this->productImageFacade->getProductImageUrl($inquiry->getProduct(), $inquiry->getDomainId()),
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    protected function getBodyVariablesReplacementsForAdmin(Inquiry $inquiry): array
    {
        return [
            ...$this->getBodyVariablesReplacements($inquiry),
            self::VARIABLE_ADMIN_INQUIRY_DETAIL_URL => fn () => $this->administrationRouter->generate(
                'admin_inquiry_detail',
                ['id' => $inquiry->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        ];
    }

    protected function getProductUrl(Inquiry $inquiry): string
    {
        if ($inquiry->getProduct() === null) {
            return '';
        }

        return $this->domainRouterFactory->getRouter($inquiry->getDomainId())->generate(
            'front_product_detail',
            ['id' => $inquiry->getProduct()->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
