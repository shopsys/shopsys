<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables;

class InquiryMailTemplateVariablesProvider
{
    /**
     * @param string $mailTemplateType
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    public function create(string $mailTemplateType): MailTemplateVariables
    {
        $mailTemplateName = $mailTemplateType === InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME
            ? t('Inquiry info sent to customer')
            : t('Inquiry info sent to administrator');

        $mailTemplateVariables = new MailTemplateVariables($mailTemplateName);

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_FULL_NAME,
            t('Customer name'),
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_EMAIL,
            t('Customer email address'),
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_TELEPHONE,
            t('Customer phone number'),
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_COMPANY_NAME,
            t('Company name'),
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_COMPANY_NUMBER,
            t('Company number'),
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_COMPANY_TAX_NUMBER,
            t('Company Tax number'),
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_NOTE,
            t('Note'),
            MailTemplateVariables::CONTEXT_BODY,
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_PRODUCT_NAME,
            t('Product name'),
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_PRODUCT_CATALOG_NUMBER,
            t('Product catnum'),
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_PRODUCT_URL,
            t('Product detail URL'),
            MailTemplateVariables::CONTEXT_BODY,
        );

        $mailTemplateVariables->addVariable(
            InquiryMail::VARIABLE_PRODUCT_IMAGE,
            t('Product image URL'),
            MailTemplateVariables::CONTEXT_BODY,
        );

        if ($mailTemplateType === InquiryMail::ADMIN_MAIL_TEMPLATE_NAME) {
            $mailTemplateVariables->addVariable(
                InquiryMail::VARIABLE_ADMIN_INQUIRY_DETAIL_URL,
                t('Admin inquiry detail URL'),
                MailTemplateVariables::CONTEXT_BODY,
            );
        }

        return $mailTemplateVariables;
    }
}
