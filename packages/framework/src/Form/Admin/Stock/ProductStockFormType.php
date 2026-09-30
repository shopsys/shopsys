<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Stock;

use Override;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockData;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Webmozart\Assert\Assert;

final class ProductStockFormType extends AbstractType
{
    public function __construct(
        private readonly StockFacade $stockFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('productQuantity', TextType::class, [
            'empty_data' => 0,
            'attr' => [
                'placeholder' => '0',
            ],
            'constraints' => [
                new Constraints\Regex(
                    pattern: '/^-?\d+$/',
                    message: 'Quantity must be an integer',
                ),
            ],
        ]);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $productStockData = $form->getData();
        Assert::isInstanceOf($productStockData, ProductStockData::class);

        $stock = $this->stockFacade->getById($productStockData->stockId);

        $view->vars['stock'] = $stock;
        $view->vars['enabled_domain_ids'] = array_keys(array_filter($stock->getEnabledIndexedByDomainId()));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductStockData::class,
            ]);
    }
}
