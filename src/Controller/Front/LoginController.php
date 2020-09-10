<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Login\LoginFormType;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class LoginController extends FrontBaseController
{
    public const SESSION_LOGIN_IN_ORDER_SUCCESS = 'login_in_order_success';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\Authenticator
     */
    private $authenticator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider
     */
    private $frontendCustomerUserProvider;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $userPasswordEncoder
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(
        Authenticator $authenticator,
        FrontendCustomerUserProvider $frontendCustomerUserProvider,
        UserPasswordEncoderInterface $userPasswordEncoder,
        SessionInterface $session
    ) {
        $this->authenticator = $authenticator;
        $this->frontendCustomerUserProvider = $frontendCustomerUserProvider;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->session = $session;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginAction(Request $request)
    {
        if ($this->isGranted(Roles::ROLE_LOGGED_CUSTOMER)) {
            return $this->redirectToRoute('front_homepage');
        }

        $form = $this->getLoginForm();

        try {
            $this->authenticator->checkLoginProcess($request);
        } catch (\Shopsys\FrameworkBundle\Model\Security\Exception\LoginFailedException $e) {
            $form->addError(new FormError(t('This account doesn\'t exist or password is incorrect')));
        }

        return $this->render('Front/Content/Login/loginForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function windowFormAction()
    {
        return $this->render('Front/Content/Login/windowForm.html.twig', [
            'form' => $this->getLoginForm()->createView(),
        ]);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getLoginForm()
    {
        return $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('front_login_check'),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginInOrderAction(Request $request)
    {
        $responseData = [
            'success' => false,
            'errorHeader' => t('Špatné přihlašovací údaje'),
            'errorMessage' => t('Zadali jste špatné uživatelské jméno nebo heslo.'),
        ];
        $formData = $request->get('front_login_form');

        try {
            $user = $this->frontendCustomerUserProvider->loadUserByUsername($formData['email']);
        } catch (UsernameNotFoundException $e) {
            $user = null;
        }

        /** @var \App\Model\Customer\User\CustomerUser $user */
        if ($user !== null && $user->getLastLogin() === null && $user->getErpCustomerNumber() !== null) {
            $responseData['errorHeader'] = t('Vaše první přihlášení');
            $responseData['errorMessage'] = t('Přihlašujete se poprvé na novém e-shopu, prosím nastavte si heslo pomocí funkce “Obnovení hesla”.');
        }

        if ($user !== null && $this->userPasswordEncoder->isPasswordValid($user, $formData['password'])) {
            $this->authenticator->loginUser($user, $request);
            $responseData['success'] = true;
            $this->session->set(self::SESSION_LOGIN_IN_ORDER_SUCCESS, true);
        }

        return new JsonResponse($responseData);
    }
}
