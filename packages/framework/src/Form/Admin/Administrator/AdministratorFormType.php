<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Administrator;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEntityField;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyUrlType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\RolesType;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class AdministratorFormType extends AbstractType
{
    public const string SCENARIO_CREATE = 'create';
    public const string SCENARIO_EDIT = 'edit';

    public function __construct(
        private readonly Security $security,
        private readonly AdministratorRoleGroupFacade $administratorRoleGroupFacade,
        private readonly RouteCsrfProtector $routeCsrfProtector,
        private readonly AccessCheckerInterface $accessChecker,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null $adminToEdit */
        $adminToEdit = $options['administrator'];

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $adminToEdit->getId(),
                    'label' => 'ID',
                ]);
        }

        $builderSettingsGroup
            ->add('username', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter username'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Username cannot be longer than {{ limit }} characters',
                    ),
                    new UniqueEntityField(
                        entityInstance: $adminToEdit,
                        message: 'Administrator with user name "{{ value }}" is already registered',
                        fieldName: 'username',
                        entityName: Administrator::class,
                    ),
                ],
                'label' => 'Login name',
            ])
            ->add('realName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter full name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Full name cannot be longer than {{ limit }} characters',
                    ),
                ],
                'label' => 'Full name',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Email(message: 'Please enter valid email'),
                    new Constraints\NotBlank(message: 'Please enter email'),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Email cannot be longer than {{ limit }} characters',
                    ),
                    new UniqueEntityField(
                        entityInstance: $adminToEdit,
                        message: 'Administrator with email "{{ value }}" is already registered',
                        fieldName: 'email',
                        entityName: Administrator::class,
                    ),
                ],
                'label' => 'Email',
            ]);

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSettingsGroup->
                add('resetPassword', DisplayOnlyUrlType::class, [
                    'route' => 'admin_administrator_send-reset-password',
                    'route_params' => [
                        'id' => $adminToEdit->getId(),
                        RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute(
                            'admin_administrator_send-reset-password',
                        ),
                    ],
                    'label' => 'Password',
                    'route_label' => t('Send reset password email'),
                    'link_target' => '_self',
                ]);
        }

        if ($this->canWorkWithRoles($adminToEdit)) {
            $builderSettingsGroup->add('roleGroup', ChoiceType::class, [
                'required' => false,
                'choices' => $this->administratorRoleGroupFacade->getAll(),
                'placeholder' => 'Custom',
                'multiple' => false,
                'label' => 'Role Group',
                'choice_label' => function (AdministratorRoleGroup $administratorRoleGroup) {
                    return $administratorRoleGroup->getName();
                },
                'attr' => [
                    'class' => 'js-role-group-select',
                ],
            ]);

            $builderSettingsGroup->add('roles', RolesType::class, [
                'label' => 'Role',
                'context' => AdminContext::class,
                'row_attr' => [
                    'data-js-role-group-custom' => null,
                    'style' => $adminToEdit !== null && $adminToEdit->getRoleGroup() !== null ? 'display: none' : '',
                ],
            ]);
        }

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_administrator_list',
                'entity' => $adminToEdit,
            ]);

        if ($options['scenario'] !== self::SCENARIO_EDIT || !$this->canWorkWithRoles($adminToEdit) || !$this->getCurrentAdministrator()->isSuperadmin()) {
            return;
        }

        $builderPromoteGroup = $builder->create('promote', GroupType::class, [
            'label' => 'Super Administrator',
        ]);

        $builderPromoteGroup->add('promoteToSuperadmin', DisplayOnlyUrlType::class, [
            'route' => 'admin_administrator_promote-to-superadmin',
            'route_params' => [
                'id' => $adminToEdit->getId(),
                RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute(
                    'admin_administrator_promote-to-superadmin',
                ),
            ],
            'route_attr' => [
                'data-confirm-window' => null,
                'data-confirm-style' => 'warning',
                'data-confirm-message' => t('Are you sure you want to promote this user to Superadmin?'),
            ],
            'label' => 'Superadmin permissions',
            'help' => 'Superadmins have all permissions including changing system configuration and access to hidden administration sections.',
            'route_label' => t('Promote user to Superadmin'),
            'link_target' => '_self',
        ]);

        $builder->add($builderPromoteGroup);
    }

    private function canWorkWithRoles(Administrator|null $adminToEdit): bool
    {
        if ($adminToEdit === null) {
            return $this->accessChecker->canCreate(AdminRoleConstant::ROLE_ADMINISTRATOR);
        }

        if ($adminToEdit->isSuperadmin()) {
            return false;
        }

        if ($this->getCurrentAdministrator()->getId() === $adminToEdit->getId()) {
            return false;
        }

        return $this->accessChecker->canEdit(AdminRoleConstant::ROLE_ADMINISTRATOR);
    }

    private function getCurrentAdministrator(): Administrator
    {
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $currentAdministrator */
        $currentAdministrator = $this->security->getUser();

        return $currentAdministrator;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['administrator', 'scenario'])
            ->setAllowedTypes('administrator', [Administrator::class, 'null'])
            ->setAllowedValues('scenario', [self::SCENARIO_CREATE, self::SCENARIO_EDIT])
            ->setDefaults([
                'data_class' => AdministratorData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
