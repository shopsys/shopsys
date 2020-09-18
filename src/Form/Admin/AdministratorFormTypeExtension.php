<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdministratorFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class AdministratorFormTypeExtension extends AbstractTypeExtension
{
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
                'attr' => ['autocomplete' => 'new-password'],
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

        $builderSettingsGroup->add('roles', ChoiceType::class, [
            'required' => false,
            'choices' => Roles::AVAILABLE_ADMINISTRATOR_ROLES,
            'placeholder' => t('-- Vyber roli --'),
            'multiple' => true,
            'label' => t('Role'),
        ]);
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
     * {@inheritDoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield AdministratorFormType::class;
    }
}
