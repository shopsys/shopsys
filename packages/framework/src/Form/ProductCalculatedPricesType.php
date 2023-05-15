<?php

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCalculatedPricesType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        private readonly PricingGroupFacade $pricingGroupFacade,
        private readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('product')
            ->setAllowedTypes('product', [Product::class, 'null']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $product = $options['product'];

        if ($product !== null) {
            $productSellingPricesIndexedByDomainId = $this->productFacade->getAllProductSellingPricesIndexedByDomainId(
                $product,
            );
            $view->vars['productSellingPricesIndexedByDomainId'] = $productSellingPricesIndexedByDomainId;
        } else {
            $view->vars['pricingGroupsIndexedByDomainId'] = $this->pricingGroupFacade->getAllIndexedByDomainId();
        }
    }
}
