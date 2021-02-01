<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $customerUser \App\Model\Customer\User\CustomerUser */
        $orderData = $options['data'];

        $domainId = Domain::SECOND_DOMAIN_ID;
        if ($orderData !== null) {
            $domainId = $orderData->domainId;
        }

        /** @var \App\Model\Order\Order $order */
        $order = $options['order'];

        $builder->get('orderItems')
            ->remove('orderPayment')
            ->remove('orderTransport');

        $builderBasicInformationGroup = $builder->get('basicInformationGroup');
        if ($order !== null) {
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
                        'data' => t(end($transactions)->getGoPayStatus()),
                    ]);
            }

            $builderBasicInformationGroup->add('transport', DisplayOnlyType::class, [
                'label' => t('Typ dopravy'),
                'data' => $order->getTransport()->getName(),
            ]);

            $builderBasicInformationGroup->add('erpNumber', DisplayOnlyType::class, [
                'label' => t('Číslo KS Moeve'),
                'position' => ['after' => 'orderNumber'],
                'data' => $order->getErpNumber()
            ]);
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'disabled' => true,
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
