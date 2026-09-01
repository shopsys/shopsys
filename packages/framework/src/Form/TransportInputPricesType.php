<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\Transport\Price\PriceWithLimitType;
use Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TransportInputPricesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vat', VatChoiceType::class, [
                'block_prefix' => 'prices_select_vat_input',
                'domain_id' => $options['domain_id'],
            ])
            ->add(
                'pricesWithLimits',
                CollectionType::class,
                [
                    'label' => false,
                    'block_prefix' => 'transport_prices_with_limits_collection',
                    'entry_type' => PriceWithLimitType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'error_bubbling' => false,
                    'entry_options' => [
                        'domain_id' => $options['domain_id'],
                        'current_transport_prices_indexed_by_id' => $options['current_transport_prices_indexed_by_id'],
                    ],
                ],
            );
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => TransportInputPricesData::class,
            ])
            ->setRequired(['domain_id', 'current_transport_prices_indexed_by_id'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('current_transport_prices_indexed_by_id', 'array');
    }
}
