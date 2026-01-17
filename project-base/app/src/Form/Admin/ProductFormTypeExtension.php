<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    public function __construct(
        private readonly FormBuilderHelper $formBuilderHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \App\Model\Product\Product|null $product */
        $product = $options['product'];

        $this->setSeoGroup($builder);
        $this->setRelatedProductsGroup($builder, $product);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    private function setSeoGroup(FormBuilderInterface $builder): void
    {
        $builderSeoGroup = $builder->get('seoGroup');

        $builderSeoGroup->remove('seoH1s');
    }

    private function setRelatedProductsGroup(FormBuilderInterface $builder, ?Product $product): void
    {
        if (!($product !== null && $product->isVariant())) {
            $relatedProductsGroupBuilder = $builder
                ->create('relatedProducts', ProductsType::class, [
                    'required' => false,
                    'main_product' => $product,
                    'label' => t('Related products'),
                ]);

            $builder->add($relatedProductsGroupBuilder);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield ProductFormType::class;
    }
}
