<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Administrator\Administrator;
use App\Model\Administrator\AdministratorTwoFactorAuthenticationFacade;
use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Controller\Admin\AdministratorController as BaseAdministratorController;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @property \App\Model\Administrator\AdministratorFacade $administratorFacade
 * @property \App\Model\Administrator\AdministratorDataFactory $administratorDataFactory
 */
class AdministratorController extends BaseAdministratorController
{
    /**
     * @var \App\Model\Administrator\AdministratorTwoFactorAuthenticationFacade
     */
    private AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorFacade;

    /**
     * @param \App\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade
     * @param \App\Model\Administrator\AdministratorDataFactory $administratorDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade $administratorRolesChangedFacade
     * @param \App\Model\Administrator\AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade
     */
    public function __construct(
        AdministratorFacade $administratorFacade,
        GridFactory $gridFactory,
        BreadcrumbOverrider $breadcrumbOverrider,
        AdministratorActivityFacade $administratorActivityFacade,
        AdministratorDataFactoryInterface $administratorDataFactory,
        AdministratorRolesChangedFacade $administratorRolesChangedFacade,
        AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade
    ) {
        parent::__construct(
            $administratorFacade,
            $gridFactory,
            $breadcrumbOverrider,
            $administratorActivityFacade,
            $administratorDataFactory,
            $administratorRolesChangedFacade
        );

        $this->administratorTwoFactorFacade = $administratorTwoFactorAuthenticationFacade;
    }

    /**
     * {@inheritDoc}
     */
    public function editAction(Request $request, int $id)
    {
        $this->denyAccessUnlessHimselfOrGranted($request, $id);

        return parent::editAction($request, $id);
    }

    /**
     * @Route("/administrator/enable-two-factor-authentication/{id}/{twoFactorAuthenticationType}", requirements={"id" = "\d+"}, name="admin_administrator_enable-two-factor-authentication")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @param string $twoFactorAuthenticationType
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enableTwoFactorAuthenticationAction(Request $request, int $id, string $twoFactorAuthenticationType): Response
    {
        if (!in_array($twoFactorAuthenticationType, Administrator::TWO_FACTOR_AUTHENTICATION_TYPES, true)) {
            $this->addErrorFlashTwig(
                t('Unsupported two factor authentication method'),
            );
            return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
        }

        $administrator = $this->administratorFacade->getById($id);
        $loggedUser = $this->getUser();
        $this->securitySafeCheck($loggedUser);

        if ($administrator->getUsername() !== $loggedUser->getUsername()) {
            $this->addErrorFlash(t('You are allowed to set up two factor authentication only to yourself.'));
            return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
        }

        if ($administrator->isEnabledTwoFactorAuth()) {
            $this->addErrorFlash(t('Two factor authentication is already enabled.'));
            return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
        }

        if ($twoFactorAuthenticationType === Administrator::TWO_FACTOR_AUTHENTICATION_TYPE_EMAIL) {
            return $this->enableEmailTwoFactorAuthentication($request, $administrator);
        }

        return $this->enableGoogleAuthTwoFactorAuthentication($request, $administrator);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Model\Administrator\Administrator $administrator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function enableEmailTwoFactorAuthentication(Request $request, Administrator $administrator): Response
    {
        $formSendEmail = $this->createSendEmailForm();
        $formVerification = $this->createVerificationForm([$this, 'validateEmailCode']);

        $formSendEmail->handleRequest($request);
        if ($formSendEmail->isSubmitted() && $formSendEmail->isValid()) {
            $this->addSuccessFlashTwig(t('An email with 6 digit code was sent to your email address.'));
            $this->administratorTwoFactorFacade->generateAndSendEmail($administrator);
        } else {
            $formVerification->handleRequest($request);
            if ($formVerification->isSubmitted() && $formVerification->isValid()) {
                $this->administratorTwoFactorFacade->enableTwoFactorAuthenticationByEmail($administrator);
                $this->addSuccessFlashTwig(t('Two factor authentication was enabled'));
                return $this->redirectToRoute('admin_administrator_edit', ['id' => $administrator->getId()]);
            }
        }

        return $this->render('Admin/Content/Administrator/enableTwoFactorAuthenticationByEmail.html.twig', [
            'formVerification' => $formVerification->createView(),
            'formSendEmail' => $formSendEmail->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Model\Administrator\Administrator $administrator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function enableGoogleAuthTwoFactorAuthentication(Request $request, Administrator $administrator): Response
    {
        $form = $this->createVerificationForm([$this, 'validateGoogleAuthCode']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->administratorTwoFactorFacade->enableTwoFactorAuthenticationByGoogleAuthenticator($administrator);
            $this->addSuccessFlashTwig(t('Two factor authentication was enabled'));
            return $this->redirectToRoute('admin_administrator_edit', ['id' => $administrator->getId()]);
        }

        if (!$administrator->hasGeneratedGoogleAuthenticatorSecret()) {
            $this->administratorTwoFactorFacade->renewGoogleAuthSecret($administrator);
        }
        $qrCodeDataUri = $this->administratorTwoFactorFacade->getQrCodeDataUri($administrator);

        return $this->render('Admin/Content/Administrator/enableTwoFactorAuthenticationByGoogleAuth.html.twig', [
            'form' => $form->createView(),
            'qrCodeDataUri' => $qrCodeDataUri,
            'googleAuthenticatorSecret' => $administrator->getGoogleAuthenticatorSecret(),
        ]);
    }

    /**
     * @param string $code
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateEmailCode(string $code, ExecutionContextInterface $context): void
    {
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();
        if ($code !== $administrator->getEmailAuthCode()) {
            $context->addViolation(t('Zadany kod neni spravny'));
        }
    }

    /**
     * @param string $code
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateGoogleAuthCode(string $code, ExecutionContextInterface $context): void
    {
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();
        if (!$this->administratorTwoFactorFacade->isGoogleAuthenticatorCodeValid($administrator, $code)) {
            $context->addViolation(t('Zadany kod neni spravny'));
        }
    }

    /**
     * @Route("/administrator/disable-two-factor-authentication/{id}", requirements={"id" = "\d+"}, name="admin_administrator_disable-two-factor-authentication")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disableTwoFactorAuthenticationAction(Request $request, int $id): Response
    {
        $administrator = $this->administratorFacade->getById($id);

        $loggedUser = $this->getUser();
        $this->securitySafeCheck($loggedUser);

        if ($administrator->getUsername() !== $loggedUser->getUsername()) {
            $this->addErrorFlash(t('You are allowed to disable two factor authentication only to yourself.'));
            return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
        }

        if ($administrator->isEmailAuthEnabled()) {
            $codeValidationCallback = [$this, 'validateEmailCode'];
        } elseif ($administrator->isGoogleAuthenticatorEnabled()) {
            $codeValidationCallback = [$this, 'validateGoogleAuthCode'];
        } else {
            $this->addErrorFlash(t('Two factor authentication is not enabled.'));
            return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
        }

        $formSendEmail = $this->createSendEmailForm();
        $formVerification = $this->createVerificationForm($codeValidationCallback);

        $formSendEmail->handleRequest($request);
        if ($formSendEmail->isSubmitted() && $formSendEmail->isValid()) {
            $this->administratorTwoFactorFacade->generateAndSendEmail($administrator);
            $this->addSuccessFlashTwig(t('An email with 6 digit code was sent to your email address.'));
        } else {
            $formVerification->handleRequest($request);
            if ($formVerification->isSubmitted() && $formVerification->isValid()) {
                $this->administratorTwoFactorFacade->disableTwoFactorAuthentication($administrator);
                $this->addSuccessFlashTwig(t('Two factor authentication was disabled'));
                return $this->redirectToRoute('admin_administrator_edit', ['id' => $administrator->getId()]);
            }
        }

        return $this->render('Admin/Content/Administrator/disableTwoFactorAuthentication.html.twig', [
            'formVerification' => $formVerification->createView(),
            'formSendEmail' => $formSendEmail->createView(),
            'administrator' => $administrator,
        ]);
    }

    /**
     * @param array $twoFactorCodeValidationCallback
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createVerificationForm(array $twoFactorCodeValidationCallback): FormInterface
    {
        $form = $this->createForm(FormType::class);
        $form->add(
            'code',
            TextType::class,
            [
                'label' => t('Authentication code'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter code']),
                    new Constraints\Callback($twoFactorCodeValidationCallback),
                ],
            ]
        );
        $form->add('verify', SubmitType::class, ['label' => t('Confirm authentication code')]);
        return $form;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface|null $loggedUser
     */
    private function securitySafeCheck(?UserInterface $loggedUser): void
    {
        if (!$loggedUser instanceof Administrator) {
            throw new AccessDeniedException(sprintf(
                'Logged user is not instance of "%s". That should not happen due to security.yaml configuration.',
                Administrator::class
            ));
        }
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createSendEmailForm(): FormInterface
    {
        /** @var \Symfony\Component\Form\FormFactoryInterface $formFactory */
        $formFactory = $this->container->get('form.factory');

        $formSendEmail = $formFactory->createNamed('formSendEmail');
        $formSendEmail->add('send', SubmitType::class, ['label' => t('Send me authentication code')]);
        return $formSendEmail;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $administratorId
     */
    private function denyAccessUnlessHimselfOrGranted(Request $request, int $administratorId): void
    {
        /** @var \App\Model\Administrator\Administrator $currentAdministrator */
        $currentAdministrator = $this->getUser();

        // always allow admin to edit himself
        if ($currentAdministrator->getId() === $administratorId) {
            return;
        }

        if ($request->getMethod() === Request::METHOD_GET) {
            $this->denyAccessUnlessGranted(Roles::ROLE_ADMINISTRATOR_VIEW);
        } else {
            $this->denyAccessUnlessGranted(Roles::ROLE_ADMINISTRATOR_FULL);
        }
    }
}
