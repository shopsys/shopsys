<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ProductsField;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Validator\Constraints\Count;

#[ContentBlock(type: 'products')]
class ProductsBlock extends AbstractContentBlock
{
    public function __construct(
        protected readonly ProductRepository $productRepository,
    ) {
    }

    #[Override]
    public function getLabel(): string
    {
        return t('Products');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:shopping-cart';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(ProductsField::class)::create('productIds')
                ->label(t('Products'))
                ->required()
                ->constraints([new Count(min: 1)]),
        ];
    }

    // products without a catalog number cannot be addressed by the storefront, so they are dropped like deleted ones
    #[Override]
    public function export(array $data, int $domainId): array
    {
        $productIds = $data['productIds'] ?? [];
        $catnumsByProductId = [];

        foreach ($this->productRepository->getAllByIds($productIds) as $product) {
            $catnumsByProductId[$product->getId()] = $product->getCatnum();
        }

        $catnums = [];

        foreach ($productIds as $productId) {
            if (isset($catnumsByProductId[$productId])) {
                $catnums[] = $catnumsByProductId[$productId];
            }
        }

        $data['catnums'] = $catnums;

        return $data;
    }
}
