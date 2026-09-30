<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Stock;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductStocksType extends AbstractType
{
    public function __construct(
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'unit_name' => null,
            'entry_type' => ProductStockFormType::class,
            'required' => false,
            'label' => false,
            'attr' => [
                'data-controller' => 'product-stocks',
            ],
        ]);
        $resolver->setAllowedTypes('unit_name', ['string', 'null']);
    }

    #[Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        $totalStockQuantitiesByDomainId = array_fill_keys($this->domain->getAllIds(), 0);

        foreach ($view->children as $childView) {
            /** @var int[] $stockEnabledDomainIds */
            $stockEnabledDomainIds = $childView->vars['enabled_domain_ids'];

            $quantity = (int)$childView->children['productQuantity']->vars['value'];

            foreach ($stockEnabledDomainIds as $domainId) {
                $totalStockQuantitiesByDomainId[$domainId] += $quantity;
            }
        }

        $view->vars['total_stock_quantities_by_domain_id'] = $totalStockQuantitiesByDomainId;
        $view->vars['unit_name'] = $options['unit_name'];
    }
}
