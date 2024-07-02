<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdministratorFormType;
use Symfony\Component\Form\AbstractTypeExtension;
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
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ],
            'first_options' => [
                'label' => t('Password'),
                'constraints' => $this->getFirstPasswordConstraints($options['scenario']),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t('Password has to include uppercase letters, lowercase letters, numbers and must be longer than 10 characters.'),
                ],
            ],
            'second_options' => [
                'label' => t('Password again'),
            ],
            'invalid_message' => 'Passwords do not match',
            'label' => t('Password'),
        ]);
    }

    /**
     * @param string $scenario
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getFirstPasswordConstraints($scenario)
    {
        $constraints = [
            new Constraints\Regex(['pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{10,}$/', 'message' => 'Password has to include uppercase letters, lowercase letters, numbers and must be longer than 10 characters.']),
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
