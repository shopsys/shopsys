<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender\Exception\NoSenderForMailTemplateException;
use Traversable;

class MailTemplateSenderFacade
{
    /**
     * @param \Traversable<int, \Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender\MailTemplateSenderInterface> $mailTemplateSenders
     */
    public function __construct(
        protected readonly Traversable $mailTemplateSenders,
        protected readonly MailTemplateFacade $mailTemplateFacade,
    ) {
    }

    public function getFormLabelForEntityIdentifier(MailTemplate $mailTemplate): ?string
    {
        return $this->getTemplateSender($mailTemplate)->getFormLabelForEntityIdentifier();
    }

    public function sendMail(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $this->getTemplateSender($mailTemplate)->sendTemplate(
            $this->mailTemplateFacade->getTemplateWrappedWithGrapesBody($mailTemplate),
            $mailTo,
            $entityId,
        );
    }

    protected function getTemplateSender(MailTemplate $mailTemplate): MailTemplateSenderInterface
    {
        foreach ($this->mailTemplateSenders as $mailTemplateSender) {
            if ($mailTemplateSender->supports($mailTemplate)) {
                return $mailTemplateSender;
            }
        }

        throw new NoSenderForMailTemplateException($mailTemplate);
    }

    public function mailSenderExists(MailTemplate $mailTemplate): bool
    {
        try {
            $this->getTemplateSender($mailTemplate);

            return true;
        } catch (NoSenderForMailTemplateException) {
            return false;
        }
    }
}
