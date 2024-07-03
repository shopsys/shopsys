<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Administrator;

use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\Constraints\FieldsAreNotIdentical;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints;

class AdministratorFormType extends AbstractType
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_EDIT = 'edit';

    /**
     * @param \Symfony\Component\Security\Core\Security $security
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Security\Roles $roles
     */
    public function __construct(
        private readonly Security $security,
        private readonly AdministratorRoleGroupFacade $administratorRoleGroupFacade,
        private readonly Roles $roles,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
        ]);

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['administrator']->getId(),
                    'label' => t('ID'),
                ]);
        }

        $builderSettingsGroup
            ->add('username', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter username']),
                    new Constraints\Length(
                        ['max' => 100, 'maxMessage' => 'Username cannot be longer than {{ limit }} characters'],
                    ),
                ],
                'label' => t('Login name'),
            ])
            ->add('realName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter full name']),
                    new Constraints\Length(
                        ['max' => 100, 'maxMessage' => 'Full name cannot be longer than {{ limit }} characters'],
                    ),
                ],
                'label' => t('Full name'),
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Email(['message' => 'Please enter valid email']),
                    new Constraints\NotBlank(['message' => 'Please enter email']),
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'Email cannot be longer than {{ limit }} characters'],
                    ),
                ],
                'label' => t('Email'),
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $options['scenario'] === self::SCENARIO_CREATE,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'label' => t('Password'),
                    'constraints' => $this->getFirstPasswordConstraints($options['scenario']),
                    'attr' => [
                        'icon' => true,
                        'iconTitle' => t(
                            'Password must be at least six characters and can\'t be the same as login name.',
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => t('Password again'),
                ],
                'invalid_message' => 'Passwords do not match',
                'label' => t('Password'),
            ]);

        if ($this->security->isGranted(Roles::ROLE_ADMINISTRATOR_FULL)) {
            $builderSettingsGroup->add('roleGroup', ChoiceType::class, [
                'required' => false,
                'choices' => $this->administratorRoleGroupFacade->getAll(),
                'placeholder' => t('Custom'),
                'multiple' => false,
                'label' => t('Role Group'),
                'choice_label' => function (AdministratorRoleGroup $administratorRoleGroup) {
                    return $administratorRoleGroup->getName();
                },
                'attr' => [
                    'class' => 'js-role-group-select',
                ],
            ]);

            $builderSettingsGroup->add('roles', ChoiceType::class, [
                'required' => false,
                'choices' => $this->roles->getAvailableAdministratorRolesChoices(),
                'placeholder' => t('-- Select a role --'),
                'multiple' => true,
                'label' => t('Role'),
                'attr' => [
                    'class' => 'js-role-group-custom',
                ],
            ]);
        } elseif ($options['administrator'] !== null) {
            $builderSettingsGroup->add('roleGroup', DisplayOnlyType::class, [
                'label' => t('Role Group'),
                'data' => $options['administrator']->getRoleGroup()?->getName() ?? t('Custom'),
            ]);

            $builderSettingsGroup->add('roles', DisplayOnlyType::class, [
                'label' => t('Role'),
                'data' => $this->getAdministratorRolesList($options['administrator']),
            ]);
        }

        $builder
            ->add($builderSettingsGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return string
     */
    private function getAdministratorRolesList(Administrator $administrator): string
    {
        $allAvailableRoleChoices = $this->roles->getAvailableAdministratorRolesChoices();
        $intersection = array_intersect($allAvailableRoleChoices, $administrator->getRoles());

        return implode(', ', array_keys($intersection));
    }

    /**
     * @param string $scenario
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getFirstPasswordConstraints($scenario)
    {
        $constraints = [
            new Constraints\Length(
                ['min' => CustomerUserPasswordFacade::MINIMUM_PASSWORD_LENGTH, 'minMessage' => 'Password must be at least {{ limit }} characters long'],
            ),
        ];

        if ($scenario === self::SCENARIO_CREATE) {
            $constraints[] = new Constraints\NotBlank([
                'message' => 'Please enter password',
            ]);
        }

        return $constraints;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['administrator', 'scenario'])
            ->setAllowedTypes('administrator', [Administrator::class, 'null'])
            ->setAllowedValues('scenario', [self::SCENARIO_CREATE, self::SCENARIO_EDIT])
            ->setDefaults([
                'data_class' => AdministratorData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new FieldsAreNotIdentical([
                        'field1' => 'username',
                        'field2' => 'password',
                        'errorPath' => 'password',
                        'message' => 'Password cannot be same as username',
                    ]),
                ],
            ]);
    }
}
