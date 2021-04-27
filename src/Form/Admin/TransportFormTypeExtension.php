<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\DisplayVariablesType;
use App\Model\Order\Mail\OrderMail;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
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

        $builderPackageTrackingGroup = $builder->create('packageTracking', GroupType::class, [
            'label' => t('Package tracking'),
        ]);

        $builderPackageTrackingGroup
            ->add('trackingUrl', TextType::class, [
                'label' => t('Tracking URL'),
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('trackingUrlVariables', DisplayVariablesType::class, [
                'label' => t('Tracking URL variables'),
                'required' => false,
                'variables' => [
                    OrderMail::TRANSPORT_VARIABLE_TRACKING_NUMBER => [
                        'text' => t('Tracking number'),
                        'required' => false,
                    ],
                ],
            ])
            ->add('trackingInstructions', LocalizedType::class, [
                'entry_type' => CKEditorType::class,
                'label' => t('Tracking instructions'),
                'required' => false,
                'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
            ])
            ->add('trackingInstructionsVariables', DisplayVariablesType::class, [
                'label' => t('Tracking instructions variables'),
                'required' => false,
                'variables' => [
                    OrderMail::TRANSPORT_VARIABLE_TRACKING_NUMBER => [
                        'text' => t('Tracking number'),
                        'required' => false,
                    ],
                    OrderMail::TRANSPORT_VARIABLE_TRACKING_URL => [
                        'text' => t('Tracking URL'),
                        'required' => false,
                    ],
                ],
            ]);

        $builder->add($builderPackageTrackingGroup);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield TransportFormType::class;
    }
}
