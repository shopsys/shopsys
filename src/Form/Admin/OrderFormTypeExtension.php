<?php

declare(strict_types=1);

namespace App\Form\Admin;

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
                        'data' => end($transactions)->getGoPayStatus(),
                    ]);
            }
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
