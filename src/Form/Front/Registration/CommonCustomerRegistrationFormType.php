<?php

declare(strict_types=1);

namespace App\Form\Front\Registration;

use App\Component\Validator\RegexValidationRule;
use Symfony\Component\Form\AbstractType;
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
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter first name']),
                    new Constraints\Length(['max' => 30, 'maxMessage' => 'First name cannot be longer than {{ limit }} characters']),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter last name']),
                    new Constraints\Length(['max' => 30, 'maxMessage' => 'Last name cannot be longer than {{ limit }} characters']),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                ],
            ]);
    }
}
