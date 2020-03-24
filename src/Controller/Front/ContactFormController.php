<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Contact\ContactFormType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormData;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Symfony\Component\HttpFoundation\Request;

class ContactFormController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade
     */
    private $contactFormFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade
     */
    private $legalConditionsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade
     */
    private $contactFormSettingsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade $contactFormFacade
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade $contactFormSettingsFacade
     */
    public function __construct(
        ContactFormFacade $contactFormFacade,
        LegalConditionsFacade $legalConditionsFacade,
        Domain $domain,
        ContactFormSettingsFacade $contactFormSettingsFacade
    ) {
        $this->contactFormFacade = $contactFormFacade;
        $this->legalConditionsFacade = $legalConditionsFacade;
        $this->domain = $domain;
        $this->contactFormSettingsFacade = $contactFormSettingsFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function indexAction(Request $request)
    {
        $domainId = $this->domain->getId();
        $privacyPolicyArticle = $this->legalConditionsFacade->findPrivacyPolicy($domainId);

        $form = $this->createForm(ContactFormType::class, new ContactFormData());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();

            try {
                $this->contactFormFacade->sendMail($contactFormData);
                $this->addSuccessFlash(t('Thank you, your message has been sent.'));
            } catch (\Shopsys\FrameworkBundle\Model\Mail\Exception\MailException $ex) {
                $this->addErrorFlash(t('Error occurred when sending email.'));
            }

            return $this->redirect($this->generateUrl('front_contact'));
        }

        return $this->render('Front/Content/ContactForm/index.html.twig', [
            'form' => $form->createView(),
            'privacyPolicyArticle' => $privacyPolicyArticle,
            'mainText' => $this->contactFormSettingsFacade->getMainText($domainId),
        ]);
    }
}
