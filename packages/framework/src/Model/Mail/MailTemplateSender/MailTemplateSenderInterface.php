<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;

interface MailTemplateSenderInterface
{
    public function getFormLabelForEntityIdentifier(): ?string;

    public function supports(MailTemplate $mailTemplate): bool;

    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void;
}
