<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ContactForm;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Twig\Environment;

class ContactFormFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Twig\Environment $twig
     */
    public function __construct(
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly Domain $domain,
        protected readonly Mailer $mailer,
        protected readonly Environment $twig,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormData $contactFormData
     */
    public function sendMail(ContactFormData $contactFormData)
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
