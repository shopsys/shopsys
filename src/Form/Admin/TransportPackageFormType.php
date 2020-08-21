<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Transport\TransportPackage\TransportPackageData;
use Shopsys\FrameworkBundle\Form\Constraints\NotNegativeMoneyAmount;
use Shopsys\FrameworkBundle\Form\DomainType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class TransportPackageFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Domain'),
            ])
            ->add('maxProductPackagesCount', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\GreaterThanOrEqual([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'value' => 1,
                    ]),
                    new Constraints\Regex([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('Maximální počet balíků (coli)'),
            ])
            ->add('maxWeight', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                    ]),
                    new Constraints\GreaterThanOrEqual([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'value' => 1,
                    ]),
                    new Constraints\Regex([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('Maximální váha'),
            ])
            ->add('priceWithVat', MoneyType::class, [
                'scale' => 6,
                'required' => true,
                'invalid_message' => 'Please enter price in correct format (positive number with decimal separator)',
                'constraints' => [
                    new Constraints\NotBlank([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'message' => 'Please enter price',
                    ]),
                    new NotNegativeMoneyAmount([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'message' => 'Price must be greater or equal to zero',
                    ]),
                ],
                'label' => t('Vstupní cena s DPH'),
            ])
            ->add('maxGirth', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\GreaterThanOrEqual([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'value' => 1,
                    ]),
                    new Constraints\Regex([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('Maximální obvod'),
            ]);

        $this->addDimensions($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function addDimensions(FormBuilderInterface $builder): void
    {
        $builder
            ->add('dimension1', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\GreaterThanOrEqual([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'value' => 1,
                    ]),
                    new Constraints\Regex([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('1. maximální rozměr balíku'),
            ])
            ->add('dimension2', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\GreaterThanOrEqual([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'value' => 1,
                    ]),
                    new Constraints\Regex([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('2. maximální rozměr balíku'),
            ])
            ->add('dimension3', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\GreaterThanOrEqual([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'value' => 1,
                    ]),
                    new Constraints\Regex([
                        'groups' => TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE,
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('3. maximální rozměr balíku'),
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TransportPackageData::class,
            ]);
    }
}
