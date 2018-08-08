<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

interface MailTemplateFactoryInterface
{

    public function create(string $name, int $domainId, MailTemplateData $data): MailTemplate;
}
