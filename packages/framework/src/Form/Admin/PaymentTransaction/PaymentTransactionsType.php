<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentTransactionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];

        $view->vars['order'] = $order;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['order'])
            ->addAllowedTypes('order', [Order::class]);
    }

    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
