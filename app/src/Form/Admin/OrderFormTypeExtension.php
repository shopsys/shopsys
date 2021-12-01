<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\GoPay\GoPayOrderStatus;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class OrderFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['order'] === null) {
            return;
        }

        /** @var \App\Model\Order\Order $order */
        $order = $options['order'];

        $builderBasicInformationGroup = $builder->get('basicInformationGroup');

        $builderBasicInformationGroup
            ->add('payment', DisplayOnlyType::class, [
                'label' => t('Typ platby'),
                'data' => $order->getPayment()->getName(),
            ]);

        if ($order->getPayment()->isGoPay() === true) {
            $transactions = $order->getGoPayTransactions();

            if (count($transactions) > 0) {
                $translatedGoPayStatus = GoPayOrderStatus::getTranslatedGoPayStatus(end($transactions)->getGoPayStatus());
            } else {
                $translatedGoPayStatus = t('Order has not been sent to GoPay');
            }

            $builderBasicInformationGroup
                ->add('gopayStatus', DisplayOnlyType::class, [
                    'label' => t('Stav platby GoPay'),
                    'data' => $translatedGoPayStatus,
                ]);
        }

        $builderBasicInformationGroup
            ->add('transport', DisplayOnlyType::class, [
                'label' => t('Typ dopravy'),
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
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield OrderFormType::class;
    }
}
