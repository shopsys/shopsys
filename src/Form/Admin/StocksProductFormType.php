<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StocksProductFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entryOptions = $options['entry_options'];
        $entryOptions['required'] = $options['required'] && $entryOptions['required'] ?? false;
        $entryOptions['constraints'] = $entryOptions['constraints'] ?? [];

        /** @var \App\Model\Stock\ProductStockData $stockProductData */
        foreach ($builder->getData() as $stockProductData) {
            if (array_key_exists($stockProductData->stockId, $options['options_by_stock_id'])) {
                $stockProductOptions = array_merge($entryOptions, $options['options_by_stock_id'][$stockProductData->stockId]);
            } else {
                $stockProductOptions = $entryOptions;
            }

            $stockProductOptions['data'] = $stockProductData;
            $stockProductOptions['label'] = $stockProductData->name;

            $builder->add($stockProductData->stockId, $options['entry_type'], $stockProductOptions);
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
            'compound' => true,
            'entry_type' => StockProductFormType::class,
            'entry_options' => [],
            'options_by_stock_id' => [],
        ]);
    }
}
