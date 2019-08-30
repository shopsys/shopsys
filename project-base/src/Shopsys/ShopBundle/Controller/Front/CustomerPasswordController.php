<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\CustomerPasswordFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\ShopBundle\Form\Front\Customer\Password\NewPasswordFormType;
use Shopsys\ShopBundle\Form\Front\Customer\Password\ResetPasswordFormType;
use Symfony\Component\HttpFoundation\Request;

class CustomerPasswordController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerPasswordFacade
     */
    private $customerPasswordFacade;

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
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerPasswordFacade $customerPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     */
    public function __construct(
        Domain $domain,
        CustomerPasswordFacade $customerPasswordFacade,
        Authenticator $authenticator
    ) {
        $this->domain = $domain;
        $this->customerPasswordFacade = $customerPasswordFacade;
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
                $this->customerPasswordFacade->resetPassword($email, $this->domain->getId());

                $this->getFlashMessageSender()->addSuccessFlashTwig(
                    t('Link to password reset sent to email <strong>{{ email }}</strong>.'),
                    [
                        'email' => $email,
                    ]
                );
                return $this->redirectToRoute('front_registration_reset_password');
            } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\UserNotFoundByEmailAndDomainException $ex) {
                $this->getFlashMessageSender()->addErrorFlashTwig(
                    t('Customer with email address <strong>{{ email }}</strong> doesn\'t exist. '
                        . '<a href="{{ registrationLink }}"> Register</a>'),
                    [
                        'email' => $ex->getEmail(),
                        'registrationLink' => $this->generateUrl('front_registration_register'),
                    ]
                );
            }
        }

        return $this->render('@ShopsysShop/Front/Content/Registration/resetPassword.html.twig', [
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

        if (!$this->customerPasswordFacade->isResetPasswordHashValid($email, $this->domain->getId(), $hash)) {
            $this->getFlashMessageSender()->addErrorFlash(t('The link to change your password expired.'));
            return $this->redirectToRoute('front_homepage');
        }

        $form = $this->createForm(NewPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $newPassword = $formData['newPassword'];

            try {
                $user = $this->customerPasswordFacade->setNewPassword($email, $this->domain->getId(), $hash, $newPassword);

                $this->authenticator->loginUser($user, $request);
            } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\UserNotFoundByEmailAndDomainException $ex) {
                $this->getFlashMessageSender()->addErrorFlashTwig(
                    t('Customer with email address <strong>{{ email }}</strong> doesn\'t exist. '
                        . '<a href="{{ registrationLink }}"> Register</a>'),
                    [
                        'email' => $ex->getEmail(),
                        'registrationLink' => $this->generateUrl('front_registration_register'),
                    ]
                );
            } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\InvalidResetPasswordHashException $ex) {
                $this->getFlashMessageSender()->addErrorFlash(t('The link to change your password expired.'));
            }

            $this->getFlashMessageSender()->addSuccessFlash(t('Password successfully changed'));
            return $this->redirectToRoute('front_homepage');
        }

        return $this->render('@ShopsysShop/Front/Content/Registration/setNewPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
