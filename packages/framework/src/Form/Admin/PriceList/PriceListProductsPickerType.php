<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PriceList;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PriceListProductsPickerType extends AbstractType
{
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domainId'] = $form->getParent()?->getData()->domainId;
        $view->vars['priceListProductPrices'] = $form->getData();
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => PriceListProductPickerType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'error_bubbling' => false,
            'renders_in_own_card' => true,
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
