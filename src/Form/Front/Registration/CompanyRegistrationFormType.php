<?php

declare(strict_types=1);

namespace App\Form\Front\Registration;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class CompanyRegistrationFormType extends RegistrationFormType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('gender');
        $builder->remove('firstName');
        $builder->remove('lastName');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildBillingAddressFormPart(FormBuilderInterface $builder, array $options): void
    {
        parent::buildBillingAddressFormPart($builder, $options);
        $builder->add(
            'companyName',
            TextType::class,
            [
                'constraints' => [
                    new Constraints\Length(['max' => 100, 'maxMessage' => 'Vyplňte prosím název společnosti kratší než {{ limit }} znaků.']),
                ],
            ]
        )->add(
            'companyNumber',
            TextType::class,
            [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím vyplňte IČ']),
                    new Constraints\Length(['max' => 50, 'maxMessage' => 'Vyplňte prosím IČ kratší než {{ limit }} znaků.']),
                ],
            ]
        )->add(
            'companyTaxNumber',
            TextType::class,
            [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(['max' => 50, 'maxMessage' => 'Vyplňte prosím DIČ/Ič DPH kratší než {{ limit }} znaků.']),
                ],
            ]
        );
        if ($options['domainId'] == 2) {
            $builder->add(
                'companyNumberWithVat',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Prosím vyplňte DIČ']),
                        new Constraints\Length(['max' => 50, 'maxMessage' => 'Vyplňte prosím DIČ kratší než {{ limit }} znaků.']),
                    ],
                ]
            );
        }
    }
}
