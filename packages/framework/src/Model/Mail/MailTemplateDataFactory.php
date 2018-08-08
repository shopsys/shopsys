<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

class MailTemplateDataFactory implements MailTemplateDataFactoryInterface
{
    public function create(): MailTemplateData
    {
        return new MailTemplateData();
    }

    public function createFromMailTemplate(MailTemplate $mailTemplate): MailTemplateData
    {
        $mailTemplateData = new MailTemplateData();
        $this->fillFromMailTemplate($mailTemplateData, $mailTemplate);

        return $mailTemplateData;
    }

    protected function fillFromMailTemplate(MailTemplateData $mailTemplateData, MailTemplate $mailTemplate): void
    {
        $mailTemplateData->name = $mailTemplate->getName();
        $mailTemplateData->bccEmail = $mailTemplate->getBccEmail();
        $mailTemplateData->subject = $mailTemplate->getSubject();
        $mailTemplateData->body = $mailTemplate->getBody();
        $mailTemplateData->sendMail = $mailTemplate->isSendMail();
    }
}
