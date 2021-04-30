<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\GoPay\GoPayOrderStatus;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

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
            $builderBasicInformationGroup
                ->add('gopayStatus', DisplayOnlyType::class, [
                    'label' => t('Stav platby GoPay'),
                    'data' => GoPayOrderStatus::getTranslatedGoPayStatus(end($transactions)->getGoPayStatus()),
                ]);
        }

        $builderBasicInformationGroup->add('transport', DisplayOnlyType::class, [
            'label' => t('Typ dopravy'),
            'data' => $order->getTransport()->getName(),
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
