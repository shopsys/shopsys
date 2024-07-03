<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdministratorFormType;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorTwoFactorAuthenticationFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingLastAdministratorException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSelfException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException;
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
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AdministratorController extends AdminBaseController
{
    protected const MAX_ADMINISTRATOR_ACTIVITIES_COUNT = 10;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactoryInterface $administratorDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade $administratorRolesChangedFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade
     */
    public function __construct(
        protected readonly AdministratorFacade $administratorFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdministratorActivityFacade $administratorActivityFacade,
        protected readonly AdministratorDataFactoryInterface $administratorDataFactory,
        protected readonly AdministratorRolesChangedFacade $administratorRolesChangedFacade,
        protected readonly AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade,
    ) {
    }

    #[Route(path: '/administrator/list/')]
    public function listAction()
    {
        $queryBuilder = $this->administratorFacade->getAllListableQueryBuilder();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'a.id');

        $grid = $this->gridFactory->create('administratorList', $dataSource);
        $grid->setDefaultOrder('realName');

        $grid->addColumn('realName', 'a.realName', t('Full name'), true);
        $grid->addColumn('email', 'a.email', t('Email'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_administrator_edit', ['id' => 'a.id']);
        $grid->addDeleteActionColumn('admin_administrator_delete', ['id' => 'a.id'])
            ->setConfirmMessage(t('Do you really want to remove this administrator?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Administrator/listGrid.html.twig');

        return $this->render('@ShopsysFramework/Admin/Content/Administrator/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    #[Route(path: '/administrator/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id)
    {
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
            try {
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

                return $this->redirectToRoute('admin_administrator_list');
            } catch (DuplicateUserNameException $ex) {
                $this->addErrorFlashTwig(
                    t('Login name <strong>{{ name }}</strong> is already used'),
                    [
                        'name' => $administratorData->username,
                    ],
                );
            }
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

        return $this->render('@ShopsysFramework/Admin/Content/Administrator/edit.html.twig', [
            'form' => $form->createView(),
            'administrator' => $administrator,
            'lastAdminActivities' => $lastAdminActivities,
        ]);
    }

    #[Route(path: '/administrator/my-account/')]
    public function myAccountAction()
    {
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $loggedUser */
        $loggedUser = $this->getUser();

        return $this->redirectToRoute('admin_administrator_edit', [
            'id' => $loggedUser->getId(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/administrator/new/')]
    public function newAction(Request $request)
    {
        $form = $this->createForm(AdministratorFormType::class, $this->administratorDataFactory->create(), [
            'scenario' => AdministratorFormType::SCENARIO_CREATE,
            'administrator' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $administratorData = $form->getData();

            try {
                $administrator = $this->administratorFacade->create($administratorData);

                $this->addSuccessFlashTwig(
                    t('Administrator <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $administrator->getRealName(),
                        'url' => $this->generateUrl('admin_administrator_edit', ['id' => $administrator->getId()]),
                    ],
                );

                return $this->redirectToRoute('admin_administrator_list');
            } catch (DuplicateUserNameException $ex) {
                $this->addErrorFlashTwig(
                    t('Login name <strong>{{ name }}</strong> is already used'),
                    [
                        'name' => $administratorData->username,
                    ],
                );
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Administrator/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     */
    #[Route(path: '/administrator/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id)
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
        } catch (DeletingSelfException $ex) {
            $this->addErrorFlash(t('You can\'t delete yourself.'));
        } catch (DeletingLastAdministratorException $ex) {
            $this->addErrorFlashTwig(
                t('Administrator <strong>{{ name }}</strong> is the only one and can\'t be deleted.'),
                [
                    'name' => $this->administratorFacade->getById($id)->getRealName(),
                ],
            );
        } catch (AdministratorNotFoundException $ex) {
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

        if ($administrator->getUsername() !== $loggedUser->getUserIdentifier()) {
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
        $formVerification = $this->createVerificationForm($this->validateEmailCode(...));

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

        return $this->render('@ShopsysFramework/Admin/Content/Administrator/enableTwoFactorAuthenticationByEmail.html.twig', [
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
        $form = $this->createVerificationForm($this->validateGoogleAuthCode(...));
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

        return $this->render('@ShopsysFramework/Admin/Content/Administrator/enableTwoFactorAuthenticationByGoogleAuth.html.twig', [
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
        if ($code !== $this->getCurrentAdministrator()->getEmailAuthCode()) {
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
    #[Route(path: '/administrator/disable-two-factor-authentication/{id}', requirements: ['id' => '\d+'], name: 'admin_administrator_disable-two-factor-authentication')]
    public function disableTwoFactorAuthenticationAction(Request $request, int $id): Response
    {
        $administrator = $this->administratorFacade->getById($id);

        $loggedUser = $this->getUser();
        $this->securitySafeCheck($loggedUser);

        if ($administrator->getUsername() !== $loggedUser->getUserIdentifier()) {
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
        $formVerification = $this->createVerificationForm($codeValidationCallback);

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

        return $this->render('@ShopsysFramework/Admin/Content/Administrator/disableTwoFactorAuthentication.html.twig', [
            'formVerification' => $formVerification->createView(),
            'formSendEmail' => $formSendEmail->createView(),
            'administrator' => $administrator,
        ]);
    }

    /**
     * @param callable $twoFactorCodeValidationCallback
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createVerificationForm(callable $twoFactorCodeValidationCallback): FormInterface
    {
        $form = $this->createForm(FormType::class);
        $form->add(
            'code',
            TextType::class,
            [
                'label' => t('Authentication code'),
                'constraints' => [
                    new NotBlank(['message' => 'Please enter code']),
                    new Callback($twoFactorCodeValidationCallback),
                ],
            ],
        );
        $form->add('verify', SubmitType::class, ['label' => t('Confirm authentication code')]);

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
        $formSendEmail->add('send', SubmitType::class, ['label' => t('Send me authentication code')]);

        return $formSendEmail;
    }
}
