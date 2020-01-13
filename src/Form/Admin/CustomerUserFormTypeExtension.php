<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerUserFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $personalDataBuilder = $builder->get('personalData');
        $personalDataBuilder->add('gender', ChoiceType::class, [
            'label' => 'Oslovení',
            'position' => 'first',
            'choices' => array_flip(CustomerUser::getAllGenders()),
            'placeholder' => t('-- Vyber oslovení --'),
            'constraints' => [
                new NotBlank(['message' => 'Please choose your gender']),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CustomerUserFormType::class;
    }
}
