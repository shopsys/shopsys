<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class MailTemplateFactory implements MailTemplateFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param string $name
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData $data
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function create(string $name, int $domainId, MailTemplateData $data): MailTemplate
    {
        $classData = $this->entityNameResolver->resolve(MailTemplate::class);

        return new $classData($name, $domainId, $data);
    }
}
