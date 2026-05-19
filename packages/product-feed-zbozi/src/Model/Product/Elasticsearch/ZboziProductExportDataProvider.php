<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product\Elasticsearch;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportDataProviderInterface;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryRepository;

class ZboziProductExportDataProvider implements ProductExportDataProviderInterface
{
    public const string ZBOZI_CATEGORY = 'zbozi_category';

    /**
     * @var array<int, string>
     */
    protected array $zboziCategoryFullNamesIndexedByProductId = [];

    public function __construct(
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ZboziCategoryRepository $zboziCategoryRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportFields(): array
    {
        return [self::ZBOZI_CATEGORY];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportFieldsByScope(): array
    {
        return [
            ProductExportScopeConfig::SCOPE_CATEGORIES => [self::ZBOZI_CATEGORY],
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function loadProductExportData(array $products, int $domainId, string $locale): void
    {
        $this->zboziCategoryFullNamesIndexedByProductId = [];

        if ($products === []) {
            return;
        }

        $productIds = array_map(
            static fn (Product $product) => $product->getId(),
            $products,
        );
        $mainCategoryIdsIndexedByProductId = $this->categoryRepository->getProductMainCategoryIdsIndexedByProductId(
            $productIds,
            $domainId,
        );

        if ($mainCategoryIdsIndexedByProductId === []) {
            return;
        }

        $zboziCategoryFullNamesIndexedByCategoryId = $this->zboziCategoryRepository->getFullNamesByCategoryIdsIndexedByCategoryId(
            array_values(array_unique($mainCategoryIdsIndexedByProductId)),
            $locale,
        );

        foreach ($mainCategoryIdsIndexedByProductId as $productId => $categoryId) {
            if (array_key_exists($categoryId, $zboziCategoryFullNamesIndexedByCategoryId)) {
                $this->zboziCategoryFullNamesIndexedByProductId[$productId] = $zboziCategoryFullNamesIndexedByCategoryId[$categoryId];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportedFieldValue(Product $product, int $domainId, string $locale, string $field): mixed
    {
        if ($field !== self::ZBOZI_CATEGORY) {
            throw new InvalidArgumentException(sprintf('There is no definition for exporting "%s" field to Elasticsearch', $field));
        }

        return $this->zboziCategoryFullNamesIndexedByProductId[$product->getId()] ?? null;
    }

    #[Override]
    public function getDefaultValue(string $field): mixed
    {
        if ($field !== self::ZBOZI_CATEGORY) {
            throw new InvalidArgumentException(sprintf('There is no default value for "%s" Elasticsearch field', $field));
        }

        return null;
    }
}
