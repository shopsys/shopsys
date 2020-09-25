<?php

declare(strict_types=1);

namespace App\Form\Front\Registration;

use App\Component\Validator\RegexValidationRule;
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
                    new Constraints\Length(['max' => 30]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                ],
            ]
        )->add(
            'companyNumber',
            TextType::class,
            [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Vyplňte prosím IČ']),
                    new Constraints\Length(['min' => 8, 'max' => 8]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::COMPANY_NUMBER_REGEX,
                        'message' => 'Prosím, zadávejte pouze čísla',
                    ]),
                ],
            ]
        )->add(
            'companyTaxNumber',
            TextType::class,
            [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(['min' => 12, 'max' => 12]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::COMPANY_TAX_NUMBER_REGEX,
                        'message' => 'Musí obsahovat pouze pouze čísla a velká písmena',
                    ]),
                ],
            ]
        );
        if ($options['domain_id'] === Domain::SECOND_DOMAIN_ID) {
            $builder->add(
                'companyNumberWithVat',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Vyplňte prosím DIČ-2']),
                        new Constraints\Length(['min' => 10, 'max' => 10]),
                        new Constraints\Regex([
                            'pattern' => RegexValidationRule::COMPANY_NUMBER_WITH_VAT_REGEX,
                            'message' => 'Prosím, zadávejte pouze čísla',
                        ]),
                    ],
                ]
            );
        }
    }
}
