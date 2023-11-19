<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Admin\PaymentTransaction\PaymentTransactionsType;
use App\Form\Admin\PaymentTransaction\PaymentTransactionType;
use App\Model\GoPay\GoPayOrderStatus;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class OrderFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['order'] === null) {
            return;
        }

        /** @var \App\Model\Order\Order $order */
        $order = $options['order'];

        $builderBasicInformationGroup = $builder->get('basicInformationGroup');

        $builderBasicInformationGroup
            ->add('payment', DisplayOnlyType::class, [
                'label' => t('Payment type'),
                'data' => $order->getPayment()->getName(),
            ]);

        if ($order->getPayment()->isGoPay() === true) {
            $goPayPaymentTransactions = $order->getGoPayTransactions();

            if (count($goPayPaymentTransactions) > 0) {
                $translatedGoPayStatus = GoPayOrderStatus::getTranslatedGoPayStatus(end($goPayPaymentTransactions)->getExternalPaymentStatus());
            } else {
                $translatedGoPayStatus = t('Order has not been sent to GoPay');
            }

            $builderBasicInformationGroup
                ->add('gopayStatus', DisplayOnlyType::class, [
                    'label' => t('GoPay payment status'),
                    'data' => $translatedGoPayStatus,
                ]);
        }

        $builderBasicInformationGroup
            ->add('transport', DisplayOnlyType::class, [
                'label' => t('Transport type'),
                'data' => $order->getTransport()->getName(),
            ])
            ->add('trackingNumber', TextType::class, [
                'label' => t('Tracking number'),
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 100,
                    ]),
                ],
            ]);

        $this->createPaymentGroup($builder, $order);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Order\Order $order
     */
    public function createPaymentGroup(FormBuilderInterface $builder, Order $order): void
    {
        $builderPaymentGroup = $builder->create('paymentGroup', GroupType::class, [
            'label' => t('Payment transactions'),
        ]);

        $builderPaymentGroup->add('paymentTransactionRefunds', PaymentTransactionsType::class, [
            'entry_type' => PaymentTransactionType::class,
            'error_bubbling' => false,
            'allow_add' => false,
            'allow_delete' => false,
            'required' => false,
            'order' => $order,
        ]);

        $builder->add($builderPaymentGroup);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield OrderFormType::class;
    }
}
