<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductQuestion\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables;

class ProductQuestionMailTemplateVariablesProvider
{
    public function create(string $mailTemplateType): MailTemplateVariables
    {
        $mailTemplateName = $mailTemplateType === ProductQuestionMail::CUSTOMER_MAIL_TEMPLATE_NAME
            ? t('Product question sent to customer')
            : t('Product question sent to administrator');

        $mailTemplateVariables = new MailTemplateVariables($mailTemplateName);

        $mailTemplateVariables->addVariable(
            ProductQuestionMail::VARIABLE_CUSTOMER_NAME,
            t('Customer name'),
        );

        $mailTemplateVariables->addVariable(
            ProductQuestionMail::VARIABLE_EMAIL,
            t('Customer email address'),
        );

        $mailTemplateVariables->addVariable(
            ProductQuestionMail::VARIABLE_QUESTION,
            t('Customer question'),
            MailTemplateVariables::CONTEXT_BODY,
        );

        $mailTemplateVariables->addVariable(
            ProductQuestionMail::VARIABLE_PRODUCT_NAME,
            t('Product name'),
        );

        $mailTemplateVariables->addVariable(
            ProductQuestionMail::VARIABLE_PRODUCT_URL,
            t('Product detail URL'),
            MailTemplateVariables::CONTEXT_BODY,
        );

        return $mailTemplateVariables;
    }
}
