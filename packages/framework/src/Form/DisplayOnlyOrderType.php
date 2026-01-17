<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DisplayOnlyOrderType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['order', 'manualDocumentNumber'])
            ->setAllowedTypes('order', [Order::class, 'null'])
            ->setAllowedTypes('manualDocumentNumber', ['string', 'null'])
            ->setDefaults([
                'mapped' => false,
                'required' => false,
                'manualDocumentNumber' => null,
                'attr' => [
                    'readonly' => 'readonly',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['order'] = $options['order'];
        $view->vars['manualDocumentNumber'] = $options['manualDocumentNumber'];
    }
}
