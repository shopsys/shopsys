<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdministratorFormType;
use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdministratorResetPasswordFormType;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactory;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorPasswordFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorTwoFactorAuthenticationFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingLastAdministratorException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSelfException;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ForRole(AdminRoleConstant::ROLE_ADMINISTRATOR)]
class AdministratorController extends AdminBaseController
{
    protected const int MAX_ADMINISTRATOR_ACTIVITIES_COUNT = 10;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactory $administratorDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade $administratorRolesChangedFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorPasswordFacade $administratorPasswordFacade
     * @param \Symfony\Bundle\SecurityBundle\Security $security
     * @param \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory
     */
    public function __construct(
        protected readonly AdministratorFacade $administratorFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdministratorActivityFacade $administratorActivityFacade,
        protected readonly AdministratorDataFactory $administratorDataFactory,
        protected readonly AdministratorRolesChangedFacade $administratorRolesChangedFacade,
        protected readonly AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade,
        protected readonly AdministratorPasswordFacade $administratorPasswordFacade,
        protected readonly Security $security,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/list/')]
    #[CanView]
    public function listAction(): Response
    {
        if ($this->getCurrentAdministrator()->isSuperadmin()) {
            $queryBuilder = $this->administratorFacade->getAllQueryBuilder();
        } else {
            $queryBuilder = $this->administratorFacade->getAllListableExcludingSuperadminQueryBuilder();
        }
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'a.id');

        $grid = $this->gridFactory->create('administratorList', $dataSource, AdminRoleConstant::ROLE_ADMINISTRATOR);
        $grid->setDefaultOrder('realName');

        $grid->addColumn('realName', 'a.realName', t('Full name'), true);
        $grid->addColumn('userName', 'a.username', t('Username'), true);
        $grid->addColumn('email', 'a.email', t('Email'));

        if ($this->getCurrentAdministrator()->isSuperadmin()) {
            $grid->addColumn('superadmin', 'is_superadmin', t('Superadmin'))
                ->setClassAttribute('text-center w-1');
        }

        $grid->addEditActionColumn('admin_administrator_edit', ['id' => 'a.id']);
        $grid->addDeleteActionColumn('admin_administrator_delete', ['id' => 'a.id'])
            ->setConfirmMessage(t('Do you really want to remove this administrator?'));

        $grid->setTheme('@ShopsysAdministration/content/administrator/listGrid.html.twig');

        return $this->render('@ShopsysAdministration/content/administrator/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/edit/{id}', requirements: ['id' => '\d+'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function editAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessHimselfOrGranted($request, $id);

        $administrator = $this->administratorFacade->getById($id);

        $loggedUser = $this->getUser();

        if (!$loggedUser instanceof Administrator) {
            throw new AccessDeniedException(sprintf(
                'Logged user is not instance of "%s". That should not happen due to security.yaml configuration.',
                Administrator::class,
            ));
        }

        if ($administrator->isSuperadmin() && !$loggedUser->isSuperadmin()) {
            $message = 'Superadmin can only be edited by superadmin.';

            throw new AccessDeniedException($message);
        }

        $administratorData = $this->administratorDataFactory->createFromAdministrator($administrator);

        $form = $this->createForm(AdministratorFormType::class, $administratorData, [
            'administrator' => $administrator,
            'scenario' => AdministratorFormType::SCENARIO_EDIT,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->administratorFacade->edit($id, $administratorData);

            if ($loggedUser->getId() === $id) {
                $this->administratorRolesChangedFacade->refreshAdministratorToken($administrator);
            }

            $this->addSuccessFlashTwig(
                t('Administrator <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $administratorData->realName,
                    'url' => $this->generateUrl('admin_administrator_edit', ['id' => $administrator->getId()]),
                ],
            );

            $redirectRouteName = $this->accessChecker->canView(AdminRoleConstant::ROLE_ADMINISTRATOR) ? 'admin_administrator_list' : 'admin_default_dashboard';

            return $this->redirectToRoute($redirectRouteName);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing administrator - %name%', ['%name%' => $administrator->getRealName()]),
        );

        $lastAdminActivities = $this->administratorActivityFacade->getLastAdministratorActivities(
            $administrator,
            static::MAX_ADMINISTRATOR_ACTIVITIES_COUNT,
        );

        return $this->render('@ShopsysAdministration/content/administrator/edit.html.twig', [
            'form' => $form->createView(),
            'administrator' => $administrator,
            'lastAdminActivities' => $lastAdminActivities,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $administratorId
     */
    protected function denyAccessUnlessHimselfOrGranted(Request $request, int $administratorId): void
    {
        $currentAdministrator = $this->getCurrentAdministrator();

        // always allow admin to edit himself
        if ($currentAdministrator->getId() === $administratorId) {
            return;
        }

        if ($request->getMethod() === Request::METHOD_GET) {
            $this->accessChecker->denyUnlessCanView(AdminRoleConstant::ROLE_ADMINISTRATOR);
        } else {
            $this->accessChecker->denyUnlessCanEdit(AdminRoleConstant::ROLE_ADMINISTRATOR);
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/my-account/')]
    #[RequireRole(SystemRole::ADMIN)]
    public function myAccountAction(): Response
    {
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $loggedUser */
        $loggedUser = $this->getUser();

        return $this->redirectToRoute('admin_administrator_edit', [
            'id' => $loggedUser->getId(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $form = $this->createForm(AdministratorFormType::class, $this->administratorDataFactory->create(), [
            'scenario' => AdministratorFormType::SCENARIO_CREATE,
            'administrator' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $administratorData = $form->getData();

            $administrator = $this->administratorFacade->create($administratorData);
            $this->administratorPasswordFacade->resetPassword($administrator->getUsername());

            $this->addSuccessFlashTwig(
                t('Administrator <strong><a href="{{ url }}">{{ name }}</a></strong> created. A link to set a password has been sent to his email.'),
                [
                    'name' => $administrator->getRealName(),
                    'url' => $this->generateUrl('admin_administrator_edit', ['id' => $administrator->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_administrator_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/administrator/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $realName = $this->administratorFacade->getById($id)->getRealName();

            $this->administratorFacade->delete($id);
            $this->addSuccessFlashTwig(
                t('Administrator <strong>{{ name }}</strong> deleted.'),
                [
                    'name' => $realName,
                ],
            );
        } catch (DeletingSelfException) {
            $this->addErrorFlash(t('You can\'t delete yourself.'));
        } catch (DeletingLastAdministratorException) {
            $this->addErrorFlashTwig(
                t('Administrator <strong>{{ name }}</strong> is the only one and can\'t be deleted.'),
                [
                    'name' => $this->administratorFacade->getById($id)->getRealName(),
                ],
            );
        } catch (AdministratorNotFoundException) {
            $this->addErrorFlash(t('Selected administrated doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_administrator_list');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @param string $twoFactorAuthenticationType
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(
        path: '/administrator/enable-two-factor-authentication/{id}/{twoFactorAuthenticationType}',
        name: 'admin_administrator_enable-two-factor-authentication',
        requirements: ['id' => '\d+'],
    )]
    #[RequireRole(SystemRole::ADMIN)]
    public function enableTwoFactorAuthenticationAction(
        Request $request,
        int $id,
        string $twoFactorAuthenticationType,
    ): Response {
        if (!in_array($twoFactorAuthenticationType, Administrator::TWO_FACTOR_AUTHENTICATION_TYPES, true)) {
            $this->addErrorFlashTwig(
                t('Unsupported two factor authentication method'),
            );

            return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
        }

        $administrator = $this->administratorFacade->getById($id);
        $loggedUser = $this->getUser();
        $this->securitySafeCheck($loggedUser);

        if ($administrator->getUsername() !== $loggedUser?->getUserIdentifier()) {
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
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function enableEmailTwoFactorAuthentication(Request $request, Administrator $administrator): Response
    {
        $formSendEmail = $this->createSendEmailForm();
        $formVerification = $this->createVerificationForm($this->validateEmailCode(...), $administrator);

        $formSendEmail->handleRequest($request);

        if ($formSendEmail->isSubmitted() && $formSendEmail->isValid()) {
            $this->addSuccessFlashTwig(t('An email with 6 digit code was sent to your email address.'));
            $this->administratorTwoFactorAuthenticationFacade->generateAndSendEmail($administrator);
        } else {
            $formVerification->handleRequest($request);

            if ($formVerification->isSubmitted() && $formVerification->isValid()) {
                $this->administratorTwoFactorAuthenticationFacade->enableTwoFactorAuthenticationByEmail($administrator);
                $this->addSuccessFlashTwig(t('Two factor authentication was enabled'));

                return $this->redirectToRoute('admin_administrator_edit', ['id' => $administrator->getId()]);
            }
        }

        return $this->render('@ShopsysAdministration/content/administrator/enableTwoFactorAuthenticationByEmail.html.twig', [
            'formVerification' => $formVerification->createView(),
            'formSendEmail' => $formSendEmail->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function enableGoogleAuthTwoFactorAuthentication(
        Request $request,
        Administrator $administrator,
    ): Response {
        $form = $this->createVerificationForm($this->validateGoogleAuthCode(...), $administrator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->administratorTwoFactorAuthenticationFacade->enableTwoFactorAuthenticationByGoogleAuthenticator($administrator);
            $this->addSuccessFlashTwig(t('Two factor authentication was enabled'));

            return $this->redirectToRoute('admin_administrator_edit', ['id' => $administrator->getId()]);
        }

        if (!$administrator->hasGeneratedGoogleAuthenticatorSecret()) {
            $this->administratorTwoFactorAuthenticationFacade->renewGoogleAuthSecret($administrator);
        }
        $qrCodeDataUri = $this->administratorTwoFactorAuthenticationFacade->getQrCodeDataUri($administrator);

        return $this->render('@ShopsysAdministration/content/administrator/enableTwoFactorAuthenticationByGoogleAuth.html.twig', [
            'form' => $form->createView(),
            'qrCodeDataUri' => $qrCodeDataUri,
            'googleAuthenticatorSecret' => $administrator->getGoogleAuthenticatorSecret(),
        ]);
    }

    /**
     * @param string $code
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    protected function validateEmailCode(string $code, ExecutionContextInterface $context): void
    {
        if ($this->getCurrentAdministrator()->getEmailAuthCode() === null || $code !== $this->getCurrentAdministrator()->getEmailAuthCode()) {
            $context->addViolation(t('Entered code is not valid'));
        }
    }

    /**
     * @param string $code
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    protected function validateGoogleAuthCode(string $code, ExecutionContextInterface $context): void
    {
        if (!$this->administratorTwoFactorAuthenticationFacade->isGoogleAuthenticatorCodeValid($this->getCurrentAdministrator(), $code)) {
            $context->addViolation(t('Entered code is not valid'));
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/disable-two-factor-authentication/{id}', name: 'admin_administrator_disable-two-factor-authentication', requirements: ['id' => '\d+'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function disableTwoFactorAuthenticationAction(Request $request, int $id): Response
    {
        $administrator = $this->administratorFacade->getById($id);

        $loggedUser = $this->getUser();
        $this->securitySafeCheck($loggedUser);

        if ($administrator->getUsername() !== $loggedUser?->getUserIdentifier()) {
            $this->addErrorFlash(t('You are allowed to disable two factor authentication only to yourself.'));

            return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
        }

        if ($administrator->isEmailAuthEnabled()) {
            $codeValidationCallback = $this->validateEmailCode(...);
        } elseif ($administrator->isGoogleAuthenticatorEnabled()) {
            $codeValidationCallback = $this->validateGoogleAuthCode(...);
        } else {
            $this->addErrorFlash(t('Two factor authentication is not enabled.'));

            return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
        }

        $formSendEmail = $this->createSendEmailForm();
        $formVerification = $this->createVerificationForm($codeValidationCallback, $administrator);

        $formSendEmail->handleRequest($request);

        if ($formSendEmail->isSubmitted() && $formSendEmail->isValid()) {
            $this->administratorTwoFactorAuthenticationFacade->generateAndSendEmail($administrator);
            $this->addSuccessFlashTwig(t('An email with 6 digit code was sent to your email address.'));
        } else {
            $formVerification->handleRequest($request);

            if ($formVerification->isSubmitted() && $formVerification->isValid()) {
                $this->administratorTwoFactorAuthenticationFacade->disableTwoFactorAuthentication($administrator);
                $this->addSuccessFlashTwig(t('Two factor authentication was disabled'));

                return $this->redirectToRoute('admin_administrator_edit', ['id' => $administrator->getId()]);
            }
        }

        return $this->render('@ShopsysAdministration/content/administrator/disableTwoFactorAuthentication.html.twig', [
            'formVerification' => $formVerification->createView(),
            'formSendEmail' => $formSendEmail->createView(),
            'administrator' => $administrator,
        ]);
    }

    /**
     * @param callable $twoFactorCodeValidationCallback
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createVerificationForm(
        callable $twoFactorCodeValidationCallback,
        Administrator $administrator,
    ): FormInterface {
        $form = $this->createForm(FormType::class);
        $form->add(
            'code',
            TextType::class,
            [
                'label' => t('Authentication code'),
                'row_attr' => ['class' => 'flex-column'],
                'label_attr' => ['class' => 'col-xl-12'],
                'constraints' => [
                    new NotBlank(message: 'Please enter code'),
                    new Callback($twoFactorCodeValidationCallback),
                ],
            ],
        );

        $label = $administrator->isEnabledTwoFactorAuth()
            ? t('Confirm code and disable two-factor authentication')
            : t('Confirm code and enable two-factor authentication');

        $form->add('verify', SubmitType::class, [
            'label' => $label,
            'row_attr' => ['class' => 'flex-column'],
            'attr' => ['class' => 'white-space-normal btn-primary mt-4'],
        ]);

        return $form;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface|null $loggedUser
     */
    protected function securitySafeCheck(?UserInterface $loggedUser): void
    {
        if (!$loggedUser instanceof Administrator) {
            throw new AccessDeniedException(sprintf(
                'Logged user is not instance of "%s". That should not happen due to security.yaml configuration.',
                Administrator::class,
            ));
        }
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createSendEmailForm(): FormInterface
    {
        /** @var \Symfony\Component\Form\FormFactoryInterface $formFactory */
        $formFactory = $this->container->get('form.factory');

        $formSendEmail = $formFactory->createNamed('formSendEmail');
        $formSendEmail->add('send', SubmitType::class, [
            'label' => t('Send me authentication code'),
            'row_attr' => ['class' => 'flex-column'],
            'attr' => ['class' => 'white-space-normal btn-primary'],
        ]);

        return $formSendEmail;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/send-reset-password/{id}', name: 'admin_administrator_send-reset-password', requirements: ['id' => '\d+'])]
    #[RequireRole(SystemRole::ADMIN)]
    #[CsrfProtection]
    public function sendResetPasswordAction(int $id): Response
    {
        $administrator = $this->administratorFacade->getById($id);

        $this->administratorPasswordFacade->resetPassword($administrator->getUsername());

        $this->addSuccessFlashTwig(
            t('Reset password request was sent to <strong>{{ email }}</strong>'),
            [
                'email' => $administrator->getEmail(),
            ],
        );

        return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/promote-to-superadmin/{id}', name: 'admin_administrator_promote-to-superadmin', requirements: ['id' => '\d+'])]
    #[SuperAdminOnly]
    #[CsrfProtection]
    public function promoteToSuperadminAction(int $id): Response
    {
        $administrator = $this->administratorFacade->getById($id);
        $administratorData = $this->administratorDataFactory->createFromAdministrator($administrator);

        $administratorData->roleGroup = null;
        $administratorData->roles = [SystemRole::SUPER_ADMIN];

        $this->administratorFacade->edit($id, $administratorData);

        $this->addSuccessFlash(
            t(
                'Administrator "%administrator_name%" now has superadmin permissions.',
                ['%administrator_name%' => $administrator->getRealName()],
            ),
        );

        return $this->redirectToRoute('admin_administrator_edit', ['id' => $id]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/administrator/set-new-password/', name: 'admin_administrator_set-new-password')]
    #[PublicAccess]
    public function setNewPasswordAction(Request $request): Response
    {
        $email = $request->query->get('email', '');
        $hash = $request->query->get('hash', '');

        $administrator = $this->administratorFacade->getByEmail($email);

        if (!$administrator->isResetPasswordHashValid($hash)) {
            return $this->render('@ShopsysAdministration/content/administrator/invalidResetPasswordHash.html.twig');
        }

        $administratorData = $this->administratorDataFactory->createFromAdministrator($administrator);

        $form = $this->createForm(AdministratorResetPasswordFormType::class, $administratorData, [
            'administrator' => $administrator,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->administratorPasswordFacade->setNewPassword(
                $administrator->getUsername(),
                $hash,
                $administratorData->password,
            );

            if (!$this->isGranted(SystemRole::ADMIN)) {
                $this->security->login($administrator, 'security.authenticator.form_login.administration');
                $request->getSession()->migrate();
            }

            $this->addSuccessFlash(t('Password has been successfully set.'));

            return $this->redirectToRoute('admin_default_dashboard');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/administrator/resetPassword.html.twig', [
            'form' => $form,
        ]);
    }
}
