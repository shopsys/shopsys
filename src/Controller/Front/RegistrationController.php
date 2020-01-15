<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Registration\RegistrationFormType;
use App\Model\Customer\User\CustomerUserUpdateDataFactory;
use App\Model\Customer\User\RegistrationDataFactoryInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\Authenticator
     */
    private $authenticator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade
     */
    private $legalConditionsFacade;

    /**
     * @var \App\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    private $customerUserUpdateDataFactory;

    /**
     * @var \App\Model\Customer\User\RegistrationDataFactoryInterface
     */
    private $registrationDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     */
    private $newsletterFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    private $countryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Customer\User\RegistrationDataFactoryInterface $registrationDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        Domain $domain,
        CustomerUserFacade $customerUserFacade,
        Authenticator $authenticator,
        LegalConditionsFacade $legalConditionsFacade,
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        RegistrationDataFactoryInterface $registrationDataFactory,
        NewsletterFacade $newsletterFacade,
        CountryFacade $countryFacade
    ) {
        $this->domain = $domain;
        $this->customerUserFacade = $customerUserFacade;
        $this->authenticator = $authenticator;
        $this->legalConditionsFacade = $legalConditionsFacade;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->registrationDataFactory = $registrationDataFactory;
        $this->newsletterFacade = $newsletterFacade;
        $this->countryFacade = $countryFacade;
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        if ($this->isGranted(Roles::ROLE_LOGGED_CUSTOMER)) {
            return $this->redirectToRoute('front_homepage');
        }

        $registrationData = $this->registrationDataFactory->createForDomainId($this->domain->getId());

        $form = $this->createForm(RegistrationFormType::class, $registrationData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationData = $form->getData();

            $countries = $this->countryFacade->getAllEnabledOnCurrentDomain();
            $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromRegistrationData($registrationData);
            $customerUserUpdateData->billingAddressData->country = $countries[0];

            $customerUser = $this->customerUserFacade->create($customerUserUpdateData);
            if ($customerUser->isNewsletterSubscription()) {
                $this->newsletterFacade->addSubscribedEmail($customerUser->getEmail(), $customerUser->getDomainId());
            }

            $this->getFlashMessageSender()->addSuccessFlash(t('You have been successfully registered.'));

            $this->authenticator->loginUser($customerUser, $request);
            return $this->redirectToRoute('front_homepage');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Front/Content/Registration/register.html.twig', [
            'form' => $form->createView(),
            'privacyPolicyArticle' => $this->legalConditionsFacade->findPrivacyPolicy($this->domain->getId()),
        ]);
    }
}
