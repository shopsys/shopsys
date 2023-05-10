<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplate as BaseMailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData as BaseMailTemplateData;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactory as BaseMailTemplateDataFactory;

/**
 * @method \App\Model\Mail\MailTemplateData create()
 * @method \App\Model\Mail\MailTemplateData createFromMailTemplate(\App\Model\Mail\MailTemplate $mailTemplate)
 * @method \App\Model\Mail\MailTemplateData[] createFromOrderStatuses(\App\Model\Order\Status\OrderStatus[] $orderStatuses, \App\Model\Mail\MailTemplate[] $mailTemplates)
 */
class MailTemplateDataFactory extends BaseMailTemplateDataFactory
{
    /**
     * @return \App\Model\Mail\MailTemplateData
     */
    protected function createInstance(): BaseMailTemplateData
    {
        return new MailTemplateData();
    }

    /**
     * @param \App\Model\Mail\MailTemplateData $mailTemplateData
     * @param \App\Model\Mail\MailTemplate $mailTemplate
     */
    protected function fillFromMailTemplate(BaseMailTemplateData $mailTemplateData, BaseMailTemplate $mailTemplate)
    {
        parent::fillFromMailTemplate($mailTemplateData, $mailTemplate);

        $mailTemplateData->orderStatus = $mailTemplate->getOrderStatus();
        $mailTemplateData->domainId = $mailTemplate->getDomainId();
    }
}
