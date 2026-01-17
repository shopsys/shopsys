<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ContactForm;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Twig\Environment;

class ContactFormFacade
{
    public function __construct(
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly Domain $domain,
        protected readonly Mailer $mailer,
        protected readonly Environment $twig,
        protected readonly MailTemplateBuilder $mailTemplateBuilder,
    ) {
    }

    public function sendMail(ContactFormData $contactFormData): void
    {
        $mainAdminMail = $this->mailSettingFacade->getMainAdminMail($this->domain->getId());
        $messageData = new MessageData(
            $mainAdminMail,
            null,
            $this->getMailBody($contactFormData),
            t('Contact form'),
            $mainAdminMail,
            $this->mailSettingFacade->getMainAdminMailName($this->domain->getId()),
        );
        $this->mailer->sendForDomain($messageData, $this->domain->getId());
    }

    protected function getMailBody(ContactFormData $contactFormData): string
    {
        $content = $this->twig->render('@ShopsysFramework/Mail/ContactForm/mail.html.twig', [
            'contactFormData' => $contactFormData,
        ]);

        return $this->mailTemplateBuilder->getMailTemplateWithContent(
            $this->domain->getId(),
            $content,
        );
    }
}
