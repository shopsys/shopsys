<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionsType;
use Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionType;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderPaymentsFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];

        $builder->add('paymentTransactionRefunds', PaymentTransactionsType::class, [
            'entry_type' => PaymentTransactionType::class,
            'error_bubbling' => false,
            'allow_add' => false,
            'allow_delete' => false,
            'required' => false,
            'order' => $order,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('order')
            ->setAllowedTypes('order', Order::class)
            ->setDefaults([
                'inherit_data' => true,
            ]);
    }
}
