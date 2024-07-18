<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData as BaseMailTemplateData;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactory as BaseMailTemplateDataFactory;

/**
 * @method \App\Model\Mail\MailTemplateData create()
 * @method \App\Model\Mail\MailTemplateData createFromMailTemplate(\App\Model\Mail\MailTemplate $mailTemplate)
 * @method \App\Model\Mail\MailTemplateData[] createFromOrderStatuses(\App\Model\Order\Status\OrderStatus[] $orderStatuses, \App\Model\Mail\MailTemplate[] $mailTemplates)
 * @method __construct(\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory)
 * @method fillFromMailTemplate(\App\Model\Mail\MailTemplateData $mailTemplateData, \App\Model\Mail\MailTemplate $mailTemplate)
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
}
