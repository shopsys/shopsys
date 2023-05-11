<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Registration\RegistrationFormType;
use App\Model\Customer\User\CustomerUserDataFactory;
use App\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade as BaseLegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends FrontBaseController
{
    private CustomerUserFacade $customerUserFacade;

    private CustomerUserDataFactory $customerUserDataFactory;

    private Domain $domain;

    private Authenticator $authenticator;

    private LegalConditionsFacade $legalConditionsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     * @param \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     */
    public function __construct(
        Domain $domain,
        CustomerUserDataFactoryInterface $customerUserDataFactory,
        CustomerUserFacade $customerUserFacade,
        Authenticator $authenticator,
        BaseLegalConditionsFacade $legalConditionsFacade
    ) {
        $this->domain = $domain;
        $this->customerUserDataFactory = $customerUserDataFactory;
        $this->customerUserFacade = $customerUserFacade;
        $this->authenticator = $authenticator;
        $this->legalConditionsFacade = $legalConditionsFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function existsEmailAction(Request $request)
    {
        $email = $request->get('email');
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $this->domain->getId());

        return new JsonResponse($customerUser !== null);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function registerAction(Request $request)
    {
        if ($this->isGranted(Roles::ROLE_LOGGED_CUSTOMER)) {
            return $this->redirectToRoute('front_homepage');
        }

        $customerUserData = $this->customerUserDataFactory->createForDomainId($this->domain->getId());

        $form = $this->createForm(RegistrationFormType::class, $customerUserData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerUserData = $form->getData();

            $customerUser = $this->customerUserFacade->register($customerUserData);
            $this->authenticator->loginUser($customerUser, $request);

            $this->addSuccessFlash(t('You have been successfully registered.'));
            return $this->redirectToRoute('front_homepage');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Front/Content/Registration/register.html.twig', [
            'form' => $form->createView(),
            'privacyPolicyArticle' => $this->legalConditionsFacade->findPrivacyPolicy($this->domain->getId()),
        ]);
    }
}
