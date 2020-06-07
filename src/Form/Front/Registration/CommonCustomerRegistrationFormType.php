<?php

declare(strict_types=1);

namespace App\Form\Front\Registration;

use App\Model\Customer\User\CustomerUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class CommonCustomerRegistrationFormType extends AbstractType
{
    /**
     * @return string|null
     */
    public function getParent()
    {
        return RegistrationFormType::class;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => array_flip(CustomerUser::getAllGenders()),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím vyberte si oslovení']),
                ],
            ])
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter first name']),
                    new Constraints\Length(['max' => 100, 'maxMessage' => 'First name cannot be longer than {{ limit }} characters']),
                ],
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter last name']),
                    new Constraints\Length(['max' => 100, 'maxMessage' => 'Last name cannot be longer than {{ limit }} characters']),
                ],
            ]);
    }
}
