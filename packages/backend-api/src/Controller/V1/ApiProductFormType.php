<?php

declare(strict_types=1);

namespace Shopsys\BackendApiBundle\Controller\V1;

use Shopsys\BackendApiBundle\Component\ApiDateTimeType\ApiDateTimeType;
use Shopsys\BackendApiBundle\Component\ApiDomainsFormType\ApiDomainsFormType;
use Shopsys\BackendApiBundle\Component\ApiLocalizedFormType\ApiLocalizedFormType;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiProductFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     */
    protected $productDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface $productDataFactory
     */
    public function __construct(ProductDataFactoryInterface $productDataFactory)
    {
        $this->productDataFactory = $productDataFactory;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hidden', CheckboxType::class);
        $builder->add('sellingDenied', CheckboxType::class, [
            'required' => true,
        ]);
        $builder->add('sellingFrom', ApiDateTimeType::class);
        $builder->add('sellingTo', ApiDateTimeType::class);
        $builder->add('catnum', TextType::class);
        $builder->add('ean', TextType::class);
        $builder->add('partno', TextType::class);
        $builder->add('name', ApiLocalizedFormType::class);
        $builder->add('shortDescription', ApiDomainsFormType::class, [
            'property_path' => 'shortDescriptions',
        ]);
        $builder->add('longDescription', ApiDomainsFormType::class, [
            'property_path' => 'descriptions',
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'data_class' => ProductData::class,
            'empty_data' => $this->productDataFactory->create(),
        ]);
    }
}
