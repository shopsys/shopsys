<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Newsletter\SubscriptionFormType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Form\FormTimeProvider;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsletterController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade
     */
    private $legalConditionsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Form\FormTimeProvider
     */
    private $formTimeProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Form\FormTimeProvider $formTimeProvider
     */
    public function __construct(
        NewsletterFacade $newsletterFacade,
        LegalConditionsFacade $legalConditionsFacade,
        Domain $domain,
        FormTimeProvider $formTimeProvider
    ) {
        $this->newsletterFacade = $newsletterFacade;
        $this->legalConditionsFacade = $legalConditionsFacade;
        $this->domain = $domain;
        $this->formTimeProvider = $formTimeProvider;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NULL
     */
    public function subscribeEmailAction(Request $request)
    {
        $form = $this->createSubscriptionForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /*
             * We submit form by ajax and don't rerender it when an error occurs,
             * so the submission date for TimedFormTypeValidator is not set correctly and we have to set it here explicit
             */
            $this->formTimeProvider->generateFormTime($form->getName());

            if ($form->isValid()) {
                $email = $form->getData()['email'];
                $this->newsletterFacade->addSubscribedEmail($email, $this->domain->getId());
                return $this->json(['success' => true]);
            } else {
                return $this->json([
                    'success' => false,
                    'errors' => $this->parseErrors($form->getErrors()),
                ]);
            }
        }

        return null;
    }

    /**
     * @param \Symfony\Component\Form\FormErrorIterator $formErrors
     * @return array
     */
    private function parseErrors(FormErrorIterator $formErrors): array
    {
        $errors = [];
        foreach ($formErrors as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscriptionAction(): Response
    {
        $form = $this->createSubscriptionForm();

        return $this->renderSubscription($form);
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function createSubscriptionForm(): Form
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm(SubscriptionFormType::class, null, [
            'action' => $this->generateUrl('front_newsletter_send'),
        ]);

        return $form;
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderSubscription(Form $form): Response
    {
        $privacyPolicyArticle = $this->legalConditionsFacade->findPrivacyPolicy($this->domain->getId());

        return $this->render('Front/Inline/Newsletter/subscription.html.twig', [
            'form' => $form->createView(),
            'success' => $form->isSubmitted() && $form->isValid(),
            'privacyPolicyArticle' => $privacyPolicyArticle,
        ]);
    }
}
