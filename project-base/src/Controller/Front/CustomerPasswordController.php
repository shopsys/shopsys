<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Customer\Password\NewPasswordFormType;
use App\Form\Front\Customer\Password\ResetPasswordFormType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Symfony\Component\HttpFoundation\Request;

class CustomerPasswordController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade
     */
    private $customerUserPasswordFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\Authenticator
     */
    private $authenticator;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     */
    public function __construct(
        Domain $domain,
        CustomerUserPasswordFacade $customerUserPasswordFacade,
        Authenticator $authenticator
    ) {
        $this->domain = $domain;
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
        $this->authenticator = $authenticator;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function resetPasswordAction(Request $request)
    {
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $email = $formData['email'];

            try {
                $this->customerUserPasswordFacade->resetPassword($email, $this->domain->getId());

                $this->addSuccessFlashTwig(
                    t('Link to password reset sent to email <strong>{{ email }}</strong>.'),
                    [
                        'email' => $email,
                    ]
                );
                return $this->redirectToRoute('front_registration_reset_password');
            } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException $ex) {
                $this->addErrorFlashTwig(
                    t('Customer with email address <strong>{{ email }}</strong> doesn\'t exist. '
                        . '<a href="{{ registrationLink }}"> Register</a>'),
                    [
                        'email' => $ex->getEmail(),
                        'registrationLink' => $this->generateUrl('front_registration_register'),
                    ]
                );
            }
        }

        return $this->render('Front/Content/Registration/resetPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setNewPasswordAction(Request $request)
    {
        $email = $request->query->get('email');
        $hash = $request->query->get('hash');

        if (!$this->customerUserPasswordFacade->isResetPasswordHashValid($email, $this->domain->getId(), $hash)) {
            $this->addErrorFlash(t('The link to change your password expired.'));
            return $this->redirectToRoute('front_homepage');
        }

        $form = $this->createForm(NewPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $newPassword = $formData['newPassword'];

            try {
                $customerUser = $this->customerUserPasswordFacade->setNewPassword($email, $this->domain->getId(), $hash, $newPassword);

                $this->authenticator->loginUser($customerUser, $request);
            } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException $ex) {
                $this->addErrorFlashTwig(
                    t('Customer with email address <strong>{{ email }}</strong> doesn\'t exist. '
                        . '<a href="{{ registrationLink }}"> Register</a>'),
                    [
                        'email' => $ex->getEmail(),
                        'registrationLink' => $this->generateUrl('front_registration_register'),
                    ]
                );
            } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\InvalidResetPasswordHashUserException $ex) {
                $this->addErrorFlash(t('The link to change your password expired.'));
            }

            $this->addSuccessFlash(t('Password successfully changed'));
            return $this->redirectToRoute('front_homepage');
        }

        return $this->render('Front/Content/Registration/setNewPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
