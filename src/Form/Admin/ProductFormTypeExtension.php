<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class ProductFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->changeSeoGroup($builder);

        $builder->add('namePrefix', LocalizedFullWidthType::class, [
            'required' => false,
            'entry_options' => [
                'constraints' => [
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Product prefix name cannot be longer than {{ limit }} characters']),
                ],
            ],
            'label' => t('Název prefix'),
            'render_form_row' => false,
            'position' => ['before' => 'name'],
        ]);

        $builder->add('nameSufix', LocalizedFullWidthType::class, [
            'required' => false,
            'entry_options' => [
                'constraints' => [
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Product suffix name cannot be longer than {{ limit }} characters']),
                ],
            ],
            'label' => t('Název suffix'),
            'render_form_row' => false,
            'position' => ['after' => 'name'],
        ]);

        $this->setShortDescriptionsUspGroup($builder, $options);

        $builder->get('displayAvailabilityGroup')->get('stockGroup')->remove('stockQuantity');
        $this->stocksGroup($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    protected function setShortDescriptionsUspGroup(FormBuilderInterface $builder, array $options): void
    {
        $builderShortDescriptionsUspGroup = $builder->create('shortDescriptionsUspGroups', GroupType::class, [
            'label' => t('Krátký popis USP'),
        ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp1', MultidomainType::class, [
                'label' => t('Krátký popis 1'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp2', MultidomainType::class, [
                'label' => t('Krátký popis 2'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp3', MultidomainType::class, [
                'label' => t('Krátký popis 3'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp4', MultidomainType::class, [
                'label' => t('Krátký popis 4'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp5', MultidomainType::class, [
                'label' => t('Krátký popis 5'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builder->add($builderShortDescriptionsUspGroup);

        /** @var \Ivory\OrderedForm\Builder\OrderedFormBuilder $shortDescriptionsUspGroups */
        $shortDescriptionsUspGroups = $builder->get('shortDescriptionsUspGroups');
        $shortDescriptionsUspGroups->setPosition(['after' => 'shortDescriptionsGroup']);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function changeSeoGroup(FormBuilderInterface $builder): void
    {
        $builderSeoGroup = $builder->get('seoGroup');

        $builderSeoGroup->remove('seoH1s');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function stocksGroup(FormBuilderInterface $builder)
    {
        $stockGroupBuilder = $builder->create('stocksGroup', GroupType::class, [
            'label' => t('Stocks'),
        ]);

//        $stockGroupBuilder->add('stockProductData', StocksProductFormType::class, [
//            'required' => false,
//            'data' => $builder->getData()->stockProductData,
//        ]);

        $stockGroupBuilder->add('stockProductData', CollectionType::class, [
            'required' => false,
            'entry_type' => StockProductFormType::class,
        ]);

        $builder->add($stockGroupBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductFormType::class;
    }
}
