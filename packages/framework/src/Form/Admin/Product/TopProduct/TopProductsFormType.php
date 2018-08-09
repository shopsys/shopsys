<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Product\TopProduct;

use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TopProductsFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer
     */
    private $removeDuplicatesTransformer;

    public function __construct(RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer)
    {
        $this->removeDuplicatesTransformer = $removeDuplicatesTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                $builder
                    ->create('products', ProductsType::class, [
                        'required' => false,
                        'sortable' => true,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer)
            )
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
