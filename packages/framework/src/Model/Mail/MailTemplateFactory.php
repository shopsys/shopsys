<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class MailTemplateFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(string $name, int $domainId, MailTemplateData $data): MailTemplate
    {
        $entityClassName = $this->entityNameResolver->resolve(MailTemplate::class);

        return new $entityClassName($name, $domainId, $data);
    }
}
