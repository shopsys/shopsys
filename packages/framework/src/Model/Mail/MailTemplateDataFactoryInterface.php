<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

interface MailTemplateDataFactoryInterface
{
    public function create(): MailTemplateData;

    public function createFromMailTemplate(MailTemplate $mailTemplate): MailTemplateData;
}
