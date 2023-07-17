<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\DisplayVariablesType;
use App\Model\Order\Mail\OrderMail;
use App\Model\Transport\Type\TransportTypeFacade;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransportFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @param \App\Model\Transport\Type\TransportTypeFacade $transportTypeFacade
     */
    public function __construct(protected TransportTypeFacade $transportTypeFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->get('basicInformation')
            ->add('transportType', ChoiceType::class, [
                'required' => true,
                'choices' => $this->transportTypeFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => t('Transport type'),
            ])
            ->add('personalPickup', YesNoType::class, [
                'required' => false,
                'label' => t('Personal pickup'),
            ])
            ->add('daysUntilDelivery', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Constraints\GreaterThanOrEqual([
                        'value' => 0,
                    ]),
                    new Constraints\Regex([
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('Days until delivery'),
            ])
            ->add('maxWeight', IntegerType::class, [
                'label' => t('Maximum weight (g)'),
                'required' => false,
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
