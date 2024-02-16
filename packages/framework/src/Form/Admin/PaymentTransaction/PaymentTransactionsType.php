<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentTransactionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];

        $view->vars['order'] = $order;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['order'])
            ->addAllowedTypes('order', [Order::class]);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
