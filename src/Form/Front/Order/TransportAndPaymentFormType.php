<?php

declare(strict_types=1);

namespace App\Form\Front\Order;

use App\Model\Order\FrontOrderData;
use Shopsys\FrameworkBundle\Form\SingleCheckboxChoiceType;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TransportAndPaymentFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(TransportFacade $transportFacade, PaymentFacade $paymentFacade)
    {
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $payments = $this->paymentFacade->getVisibleByDomainId($options['domain_id']);
        $transports = $this->transportFacade->getVisibleByDomainId($options['domain_id'], $payments);

        $builder
            ->add('transportsByProductTypeId', CollectionType::class, [
                'entry_type' => SingleCheckboxChoiceType::class,
                'allow_add' => true,
                'entry_options' => [
                    'choices' => $transports,
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'constraints' => [
                        new Constraints\NotNull(['message' => 'Please choose shipping type']),
                    ],
                    'invalid_message' => 'Please choose shipping type',
                ],
            ])
            ->add('payment', SingleCheckboxChoiceType::class, [
                'choices' => $payments,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotNull(['message' => 'Please choose payment type']),
                ],
                'invalid_message' => 'Please choose payment type',
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Constraints\Callback([$this, 'validateTransportPaymentRelation']),
                ],
            ]);
    }

    /**
     * @param \App\Model\Order\FrontOrderData $orderData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateTransportPaymentRelation(FrontOrderData $orderData, ExecutionContextInterface $context): void
    {
        $payment = $orderData->payment;
        $transports = array_filter($orderData->transportsByProductTypeId); // filter NULL values

        $relationExists = false;
        if ($payment instanceof Payment && count($transports) > 0) {
            $relationExists = true;
            foreach ($transports as $transport) {
                if (in_array($transport, $payment->getTransports(), true) === false) {
                    $relationExists = false;
                    break;
                }
            }
        }

        if ($relationExists === false) {
            $context->addViolation('Please choose a valid combination of transports and payment');
        }
    }
}
