<?php

namespace Shopsys\FrameworkBundle\Model\ContactForm;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Twig\Environment;

class ContactFormFacade
{
    protected MailSettingFacade $mailSettingFacade;

    protected Domain $domain;

    protected Mailer $mailer;

    protected Environment $twig;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Twig\Environment $twig
     */
    public function __construct(
        MailSettingFacade $mailSettingFacade,
        Domain $domain,
        Mailer $mailer,
        Environment $twig
    ) {
        $this->mailSettingFacade = $mailSettingFacade;
        $this->domain = $domain;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormData $contactFormData
     */
    public function sendMail(ContactFormData $contactFormData)
    {
        $messageData = new MessageData(
            $this->mailSettingFacade->getMainAdminMail($this->domain->getId()),
            null,
            $this->getMailBody($contactFormData),
            t('Contact form'),
            $contactFormData->email,
            $contactFormData->name
        );
        $this->mailer->send($messageData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormData $contactFormData
     * @return string
     */
    protected function getMailBody($contactFormData)
    {
        return $this->twig->render('@ShopsysFramework/Mail/ContactForm/mail.html.twig', [
            'contactFormData' => $contactFormData,
        ]);
    }
}
