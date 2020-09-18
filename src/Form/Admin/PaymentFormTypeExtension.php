<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentFacade;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Payment\PaymentFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PaymentFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade
     */
    private $goPayPaymentMethodFacade;

    /**
     * @var PaymentFacade
     */
    private PaymentFacade $paymentFacade;

    /**
     * @var Payment|null
     */
    private $payment;

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $goPayPaymentMethodFacade
     */
    public function __construct(GoPayPaymentMethodFacade $goPayPaymentMethodFacade, PaymentFacade $paymentFacade)
    {
        $this->goPayPaymentMethodFacade = $goPayPaymentMethodFacade;
        $this->paymentFacade = $paymentFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->payment = $options['payment'];

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
            ])
            ->add('isOverLimitPayment', YesNoType::class, [
                'label' => t('Platba pro nadlimitní množství'),
                'required' => false,
            ])
            ->add('externalId', IntegerType::class, [
                'label' => t('Párovací ID můstku'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Callback([
                        'callback' => [$this, 'validateUniqueExternalId']
                    ])
                ]
            ])
            ->add('meanOfPayment', TextType::class, [
                'label' => t('Moeve - MeanOfPayment'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ]
            ]);

        if ($options['payment'] !== null) {
            /** @var \App\Model\Payment\Payment $payment */
            $payment = $options['payment'];
            if ($payment->isHiddenByGoPay()) {
                $builderBasicInformationGroup->add('hidden', YesNoType::class, [
                    'label' => t('Hidden'),
                    'required' => false,
                    'disabled' => true,
                    'attr' => [
                        'icon' => true,
                        'iconTitle' => t('Tento způsob platby je skrytý systémem GoPay.'),
                    ],
                ]);
            }
        }
    }

    /**
     * @param int|null $id
     * @param ExecutionContextInterface $context
     */
    public function validateUniqueExternalId(?int $id, ExecutionContextInterface $context): void
    {
        if ($id === null) {
            return;
        }

        $existingPayment = $this->paymentFacade->findByExternalId($id);
        if ($existingPayment !== null) {
            if ($this->payment === null || $existingPayment->getId() !== $this->payment->getId()) {
                $context->buildViolation(sprintf(
                    t('Zadané párovací ID můstku je již použito u jiné platby (%s)'),
                    $existingPayment->getName()
                ))
                    ->atPath('externalId')
                    ->addViolation();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield PaymentFormType::class;
    }
}
