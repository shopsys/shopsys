<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\ProductIdToProductTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

final class ProductType extends AbstractType
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly ProductIdToProductTransformer $productIdToProductTransformer,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->productIdToProductTransformer);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'placeholder' => t('Choose product'),
            'picker_title' => t('Assign product'),
            'enable_remove' => false,
            'item_name' => 'name',
            'allow_main_variants' => true,
            'allow_variants' => true,
            'required' => true,
        ]);

        $resolver->setDefault('picker_url', function (Options $options): string {
            return $this->router->generate('admin_productpicker_picksingle', [
                'jsInstanceId' => '__js_instance_id__',
                'allowMainVariants' => $options['allow_main_variants'],
                'allowVariants' => $options['allow_variants'],
            ]);
        });

        $resolver->setAllowedTypes('allow_main_variants', 'bool');
        $resolver->setAllowedTypes('allow_variants', 'bool');
    }

    #[Override]
    public function getParent(): string
    {
        return SinglePickerType::class;
    }
}
