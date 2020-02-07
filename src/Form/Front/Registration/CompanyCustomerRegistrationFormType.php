<?php

declare(strict_types=1);

namespace App\Form\Front\Registration;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class CompanyCustomerRegistrationFormType extends AbstractType
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
        $builder->add(
            'companyName',
            TextType::class,
            [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Vyplňte prosím název společnosti']),
                    new Constraints\Length(['max' => 100, 'maxMessage' => 'Vyplňte prosím název společnosti kratší než {{ limit }} znaků.']),
                ],
            ]
        )->add(
            'companyNumber',
            TextType::class,
            [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Vyplňte prosím IČ']),
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
        if ($options['domainId'] == Domain::SECOND_DOMAIN_ID) {
            $builder->add(
                'companyNumberWithVat',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Vyplňte prosím DIČ']),
                        new Constraints\Length(['max' => 50, 'maxMessage' => 'Vyplňte prosím DIČ kratší než {{ limit }} znaků.']),
                    ],
                ]
            );
        }
    }
}
