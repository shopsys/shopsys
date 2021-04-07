<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransportFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->get('basicInformation')
            ->add('personalPickup', YesNoType::class, [
                'required' => false,
                'label' => t('Osobní odběr Commerce Cloud'),
            ])
            ->add('isOverLimitTransport', YesNoType::class, [
                'label' => t('Doprava pro nadlimitní množství'),
                'required' => false,
            ])
            ->add('daysUntilDelivery', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\GreaterThanOrEqual([
                        'value' => 0,
                    ]),
                    new Constraints\Regex([
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('Dnů do doručení'),
            ])
            ->add('deliveryCode', TextType::class, [
                'label' => t('Moewe - DeliveryCode'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 10,
                    ]),
                ],
            ])
            ->add('typeOfDeliveryKey', IntegerType::class, [
                'label' => t('Moewe - TypeOfDeliveryKey'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0),
                    new LessThanOrEqual(99),
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield TransportFormType::class;
    }
}
