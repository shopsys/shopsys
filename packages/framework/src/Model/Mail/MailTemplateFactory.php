<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

class MailTemplateFactory implements MailTemplateFactoryInterface
{
    public function create(string $name, int $domainId, MailTemplateData $data): MailTemplate
    {
        return new MailTemplate($name, $domainId, $data);
    }
}
