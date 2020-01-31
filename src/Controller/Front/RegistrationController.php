<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Registration\CompanyRegistrationFormType;
use App\Form\Front\Registration\RegistrationFormType;
use App\Model\Customer\User\RegistrationDataFactoryInterface;
use App\Model\Customer\User\RegistrationFacadeInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
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
     * @var \App\Model\Customer\User\RegistrationDataFactoryInterface
     */
    private $registrationDataFactory;

    /**
     * @var \App\Model\Customer\User\RegistrationFacadeInterface
     */
    private $registrationFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \App\Model\Customer\User\RegistrationDataFactoryInterface $registrationDataFactory
     * @param \App\Model\Customer\User\RegistrationFacadeInterface $registrationFacade
     */
    public function __construct(
        Domain $domain,
        CustomerUserFacade $customerUserFacade,
        Authenticator $authenticator,
        LegalConditionsFacade $legalConditionsFacade,
        RegistrationDataFactoryInterface $registrationDataFactory,
        RegistrationFacadeInterface $registrationFacade
    ) {
        $this->domain = $domain;
        $this->customerUserFacade = $customerUserFacade;
        $this->authenticator = $authenticator;
        $this->legalConditionsFacade = $legalConditionsFacade;
        $this->registrationDataFactory = $registrationDataFactory;
        $this->registrationFacade = $registrationFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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
        $active = ['common' => true, 'company' => false];
        $registrationData = $this->registrationDataFactory->createForDomainId($this->domain->getId());

        $form = $this->createForm(RegistrationFormType::class, $registrationData);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $registrationData = $form->getData();

                $customerUser = $this->registrationFacade->register($registrationData);

                $this->getFlashMessageSender()->addSuccessFlash(t('You have been successfully registered.'));

                $this->authenticator->loginUser($customerUser, $request);
                return $this->redirectToRoute('front_homepage');
            } else {
                $this->getFlashMessageSender()->addErrorFlash(t('Please check the correctness of all data filled.'));
            }
            $active['common'] = true;
        }

        $companyRegistrationForm = $this->createForm(CompanyRegistrationFormType::class, $registrationData);
        $companyRegistrationForm->handleRequest($request);
        if ($companyRegistrationForm->isSubmitted()) {
            if ($companyRegistrationForm->isValid()) {

                /** @var \App\Model\Customer\User\RegistrationData $registrationData */
                $registrationData = $companyRegistrationForm->getData();
                $customerUser = $this->registrationFacade->registerCompany($registrationData);
                $this->getFlashMessageSender()->addSuccessFlash(t('You have been successfully registered.'));

                $this->authenticator->loginUser($customerUser, $request);
                return $this->redirectToRoute('front_homepage');
            } else {
                $this->getFlashMessageSender()->addErrorFlash(t('Please check the correctness of all data filled.'));
            }
            $active['common'] = false;
            $active['company'] = true;
        }

        return $this->render('Front/Content/Registration/register.html.twig', [
            'form' => $form->createView(),
            'companyForm' => $companyRegistrationForm->createView(),
            'privacyPolicyArticle' => $this->legalConditionsFacade->findPrivacyPolicy($this->domain->getId()),
            'activeCards' => $active,
            'domain' => $this->domain,
        ]);
    }
}
