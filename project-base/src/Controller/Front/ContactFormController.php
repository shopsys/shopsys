<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Contact\ContactFormType;
use App\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormData;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade as BaseLegalConditionsFacade;
use Symfony\Component\HttpFoundation\Request;

class ContactFormController extends FrontBaseController
{
    private ContactFormFacade $contactFormFacade;

    private LegalConditionsFacade $legalConditionsFacade;

    private Domain $domain;

    private ContactFormSettingsFacade $contactFormSettingsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormFacade $contactFormFacade
     * @param \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade $contactFormSettingsFacade
     */
    public function __construct(
        ContactFormFacade $contactFormFacade,
        BaseLegalConditionsFacade $legalConditionsFacade,
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

            $this->contactFormFacade->sendMail($contactFormData);
            $this->addSuccessFlash(t('Thank you, your message has been sent.'));

            return $this->redirect($this->generateUrl('front_contact'));
        }

        return $this->render('Front/Content/ContactForm/index.html.twig', [
            'form' => $form->createView(),
            'privacyPolicyArticle' => $privacyPolicyArticle,
            'mainText' => $this->contactFormSettingsFacade->getMainText($domainId),
        ]);
    }
}
