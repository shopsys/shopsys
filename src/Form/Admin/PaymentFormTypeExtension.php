<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Payment\PaymentFormType;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use App\Model\Payment\Payment;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PaymentFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade
     */
    private $goPayPaymentMethodFacade;

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $goPayPaymentMethodFacade
     */
    public function __construct(GoPayPaymentMethodFacade $goPayPaymentMethodFacade)
    {
        $this->goPayPaymentMethodFacade = $goPayPaymentMethodFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderBasicInformationGroup = $builder->get('basicInformation');

        $builderBasicInformationGroup
            ->add('type', ChoiceType::class, [
                'label' => t('Type'),
                'choices' => [
                    t('Basic') => Payment::TYPE_BASIC,
                    t('GoPay') => Payment::TYPE_GOPAY,
                ],
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'attr' => [
                    'class' => 'js-payment-type',
                ],
            ])
            ->add('goPayPaymentMethod', ChoiceType::class, [
                'label' => t('GoPay payment method'),
                'choices' => $this->goPayPaymentMethodFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'attr' => [
                    'class' => 'js-payment-gopay-payment-method',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield PaymentFormType::class;
    }
}
