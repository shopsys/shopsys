<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables;

class WatchdogMailTemplateVariablesProvider
{
    public function create(): MailTemplateVariables
    {
        $mailTemplateName = t('Watchdog');

        $mailTemplateVariables = new MailTemplateVariables($mailTemplateName);

        $mailTemplateVariables->addVariable(
            WatchdogMail::VARIABLE_PRODUCT_NAME,
            t('Product name'),
        );

        $mailTemplateVariables->addVariable(
            WatchdogMail::VARIABLE_PRODUCT_QUANTITY,
            t('Product stock quantity'),
            MailTemplateVariables::CONTEXT_BODY,
        );

        $mailTemplateVariables->addVariable(
            WatchdogMail::VARIABLE_PRODUCT_URL,
            t('Product detail URL'),
            MailTemplateVariables::CONTEXT_BODY,
        );

        $mailTemplateVariables->addVariable(
            WatchdogMail::VARIABLE_PRODUCT_IMAGE,
            t('Product image URL'),
            MailTemplateVariables::CONTEXT_BODY,
        );

        return $mailTemplateVariables;
    }
}
