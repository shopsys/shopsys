<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Administrator\Administrator;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdministratorFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints;

class AdministratorFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Symfony\Component\Security\Core\Security
     */
    private Security $security;

    /**
     * @var \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade
     */
    private AdministratorRoleGroupFacade $administratorRoleGroupFacade;

    /**
     * @param \Symfony\Component\Security\Core\Security $security
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     */
    public function __construct(Security $security, AdministratorRoleGroupFacade $administratorRoleGroupFacade)
    {
        $this->security = $security;
        $this->administratorRoleGroupFacade = $administratorRoleGroupFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderSettingsGroup = $builder->get('settings');
        $builderSettingsGroup->remove('password');
        $builderSettingsGroup->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => $options['scenario'] === AdministratorFormType::SCENARIO_CREATE,
            'options' => [
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ],
            'first_options' => [
                'label' => t('Password'),
                'constraints' => $this->getFirstPasswordConstraints($options['scenario']),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t('Heslo musí obsahovat velké, malé písmena, číslice a musí být delší než 10 znaků.'),
                ],
            ],
            'second_options' => [
                'label' => t('Password again'),
            ],
            'invalid_message' => 'Passwords do not match',
            'label' => t('Password'),
        ]);

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

        if ($this->security->isGranted(Roles::ROLE_ADMINISTRATOR_FULL)) {
            $builderSettingsGroup->add('roles', ChoiceType::class, [
                'required' => false,
                'choices' => Roles::getAvailableAdministratorRolesChoices(),
                'placeholder' => t('-- Vyber roli --'),
                'multiple' => true,
                'label' => t('Role'),
                'attr' => [
                    'class' => 'js-role-group-custom',
                ],
            ]);
        } elseif ($options['administrator'] !== null) {
            $builder->add('roles', DisplayOnlyType::class, [
                'label' => t('Role'),
                'data' => $this->getAdministratorRolesList($options['administrator']),
                'attr' => [
                    'class' => 'js-role-group-custom',
                ],
            ]);
        }
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     * @return string
     */
    private function getAdministratorRolesList(Administrator $administrator): string
    {
        $allAvailableRoleChoices = Roles::getAvailableAdministratorRolesChoices();
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
            new Constraints\Regex(['pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{10,}$/', 'message' => 'Heslo musí obsahovat velké, malé písmena, číslice a musí být delší než 10 znaků.']),
        ];

        if ($scenario === AdministratorFormType::SCENARIO_CREATE) {
            $constraints[] = new Constraints\NotBlank([
                'message' => 'Please enter password',
            ]);
        }

        return $constraints;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield AdministratorFormType::class;
    }
}
