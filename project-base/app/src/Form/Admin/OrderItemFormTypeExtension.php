<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderItemFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        /*
         * Data class is set to extended OrderItemData, so when a new item is created in collection form type, a proper data object will be created
         */
        $resolver->setDefault('data_class', OrderItemData::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield OrderItemFormType::class;
    }
}
