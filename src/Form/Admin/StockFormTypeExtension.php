<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Stock\Stock;
use App\Model\Stock\StockData;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class StockFormTypeExtension extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $stockDataBuilder = $builder->create('stockData', GroupType::class, [
            'label' => t('Stock'),
        ]);

        if ($options['stock'] === null) {
            $stockDataBuilder
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'data' => $options['domain_id'],
                    'label' => t('Domain'),
                ]);
        }

        $stockDataBuilder->add('name', TextType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter stock name']),
            ],
            'label' => t('Name'),
        ])
        ->add('centralStock', YesNoType::class, [
            'required' => false,
            'label' => t('Central stock'),
        ])
        ->add(
            'externalId',
            TextType::class,
            [
                'required' => false,
                'label' => t('External bridge ID'),
            ]
        );

        $builder->add($stockDataBuilder);
        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['stock', 'domain_id'])
            ->setAllowedTypes('stock', [Stock::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'data_class' => StockData::class,
            ]);
    }
}
