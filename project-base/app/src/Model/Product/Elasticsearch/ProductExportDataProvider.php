<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Product;
use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportDataProviderInterface;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeRule;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductSellableVariantsProvider;

final class ProductExportDataProvider implements ProductExportDataProviderInterface
{
    public const string MAIN_CATEGORY_PATH = 'main_category_path';
    public const string USPS = 'usps';
    public const string SEARCHING_NAMES = 'searching_names';
    public const string SEARCHING_DESCRIPTIONS = 'searching_descriptions';
    public const string SEARCHING_CATNUMS = 'searching_catnums';
    public const string SEARCHING_EANS = 'searching_eans';
    public const string SEARCHING_PARTNOS = 'searching_partnos';
    public const string SEARCHING_SHORT_DESCRIPTIONS = 'searching_short_descriptions';
    public const string BREADCRUMB = 'breadcrumb';

    public const string SCOPE_DESCRIPTION = 'product_description_scope';
    public const string SCOPE_SHORT_DESCRIPTION = 'product_short_description_scope';
    public const string SCOPE_CATNUM = 'product_catnum_scope';
    public const string SCOPE_EAN = 'product_ean_scope';
    public const string SCOPE_PARTNO = 'product_partno_scope';

    private const string VALUE_SEPARATOR = ' ';

    public function __construct(
        private readonly CategoryFacade $categoryFacade,
        private readonly ProductSellableVariantsProvider $productSellableVariantsProvider,
        private readonly BreadcrumbFacade $breadcrumbFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportFields(): array
    {
        return [
            self::MAIN_CATEGORY_PATH,
            self::USPS,
            self::SEARCHING_NAMES,
            self::SEARCHING_DESCRIPTIONS,
            self::SEARCHING_CATNUMS,
            self::SEARCHING_EANS,
            self::SEARCHING_PARTNOS,
            self::SEARCHING_SHORT_DESCRIPTIONS,
            self::BREADCRUMB,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportScopeRules(): array
    {
        return [
            ProductExportScopeConfig::SCOPE_CATEGORIES => new ProductExportScopeRule([
                self::MAIN_CATEGORY_PATH,
                self::BREADCRUMB,
            ]),
            ProductExportScopeConfig::SCOPE_VARIANTS => new ProductExportScopeRule([
                ProductExportFieldProvider::PARAMETERS,
                self::SEARCHING_NAMES,
                self::SEARCHING_DESCRIPTIONS,
                self::SEARCHING_CATNUMS,
                self::SEARCHING_EANS,
                self::SEARCHING_PARTNOS,
                self::SEARCHING_SHORT_DESCRIPTIONS,
            ]),
            ProductExportScopeConfig::SCOPE_NAME => new ProductExportScopeRule([
                self::SEARCHING_NAMES,
                self::BREADCRUMB,
            ]),
            self::SCOPE_DESCRIPTION => new ProductExportScopeRule([
                ProductExportFieldProvider::DESCRIPTION,
                self::SEARCHING_DESCRIPTIONS,
            ]),
            self::SCOPE_SHORT_DESCRIPTION => new ProductExportScopeRule([
                ProductExportFieldProvider::SHORT_DESCRIPTION,
                self::SEARCHING_SHORT_DESCRIPTIONS,
            ]),
            self::SCOPE_CATNUM => new ProductExportScopeRule([
                ProductExportFieldProvider::CATNUM,
                self::SEARCHING_CATNUMS,
            ]),
            self::SCOPE_EAN => new ProductExportScopeRule([
                ProductExportFieldProvider::EAN,
                self::SEARCHING_EANS,
            ]),
            self::SCOPE_PARTNO => new ProductExportScopeRule([
                ProductExportFieldProvider::PARTNO,
                self::SEARCHING_PARTNOS,
            ]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function loadProductExportData(array $products, int $domainId, string $locale): void
    {
        $this->productSellableVariantsProvider->resetCache();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportedFieldValue(BaseProduct $product, int $domainId, string $locale, string $field): mixed
    {
        if (!$product instanceof Product) {
            throw new InvalidArgumentException(sprintf('Product must be instance of "%s"', Product::class));
        }

        return match ($field) {
            self::MAIN_CATEGORY_PATH => $this->extractMainCategoryPath($product, $domainId, $locale),
            self::USPS => $product->getAllNonEmptyShortDescriptionUsp($domainId),
            self::SEARCHING_NAMES => $this->extractSearchingNames($product, $domainId, $locale),
            self::SEARCHING_DESCRIPTIONS => $this->extractSearchingDescriptions($product, $domainId),
            self::SEARCHING_CATNUMS => $this->extractSearchingCatnums($product, $domainId),
            self::SEARCHING_EANS => $this->extractSearchingEans($product, $domainId),
            self::SEARCHING_PARTNOS => $this->extractSearchingPartnos($product, $domainId),
            self::SEARCHING_SHORT_DESCRIPTIONS => $this->extractSearchingShortDescriptions($product, $domainId),
            self::BREADCRUMB => $this->extractBreadcrumb($product, $domainId, $locale),
            default => throw new InvalidArgumentException(sprintf('There is no definition for exporting "%s" field to Elasticsearch', $field)),
        };
    }

    #[Override]
    public function getDefaultValue(string $field): mixed
    {
        return match ($field) {
            self::MAIN_CATEGORY_PATH,
            self::SEARCHING_NAMES,
            self::SEARCHING_DESCRIPTIONS,
            self::SEARCHING_CATNUMS,
            self::SEARCHING_EANS,
            self::SEARCHING_PARTNOS,
            self::SEARCHING_SHORT_DESCRIPTIONS => '',
            self::USPS,
            self::BREADCRUMB => [],
            default => throw new InvalidArgumentException(sprintf('There is no default value for "%s" Elasticsearch field', $field)),
        };
    }

    private function extractSearchingCatnums(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantCatnums = [];
            $variantCatnums[] = $product->getCatnum();

            foreach ($this->getVariantsForDefaultPricingGroup($product, $domainId) as $variant) {
                $variantCatnums[] = $variant->getCatnum();
            }

            return $variantCatnums
                |> array_unique(...)
                |> (fn ($v) => implode(self::VALUE_SEPARATOR, $v))
                |> trim(...);
        }

        return $product->getCatnum();
    }

    private function extractSearchingEans(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantEans = [];
            $variantEans[] = $product->getEan() ?? '';

            foreach ($this->getVariantsForDefaultPricingGroup($product, $domainId) as $variant) {
                $variantEans[] = $variant->getEan() ?? '';
            }

            return $variantEans
                |> array_unique(...)
                |> (fn ($v) => implode(self::VALUE_SEPARATOR, $v))
                |> trim(...);
        }

        return $product->getEan() ?? '';
    }

    private function extractSearchingPartnos(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantPartnos = [];
            $variantPartnos[] = $product->getPartno() ?? '';

            foreach ($this->getVariantsForDefaultPricingGroup($product, $domainId) as $variant) {
                $variantPartnos[] = $variant->getPartno() ?? '';
            }

            return $variantPartnos
                |> array_unique(...)
                |> (fn ($v) => implode(self::VALUE_SEPARATOR, $v))
                |> trim(...);
        }

        return $product->getPartno() ?? '';
    }

    private function extractSearchingNames(Product $product, int $domainId, string $locale): string
    {
        if ($product->isMainVariant()) {
            $variantNames = $product->getFullName($locale);

            foreach ($this->getVariantsForDefaultPricingGroup($product, $domainId) as $variant) {
                $variantFullName = $variant->getFullName($locale);

                if ($variantFullName !== '' && !str_contains($variantNames, $variantFullName)) {
                    $variantNames .= self::VALUE_SEPARATOR . $variantFullName;
                }
            }

            return trim($variantNames);
        }

        return $product->getFullName($locale);
    }

    private function extractSearchingDescriptions(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantDescriptions = $product->getDescription($domainId) ?? '';

            foreach ($this->getVariantsForDefaultPricingGroup($product, $domainId) as $variant) {
                $variantDescription = $variant->getDescription($domainId);

                if ($variantDescription !== null && $variantDescription !== '' && strpos($variantDescriptions, $variantDescription) === false) {
                    $variantDescriptions .= self::VALUE_SEPARATOR . $variantDescription;
                }
            }

            return trim($variantDescriptions);
        }

        return $product->getDescription($domainId) ?? '';
    }

    private function extractSearchingShortDescriptions(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantDescriptions = $product->getShortDescription($domainId) ?? '';

            foreach ($this->getVariantsForDefaultPricingGroup($product, $domainId) as $variant) {
                $variantDescription = $variant->getShortDescription($domainId);

                if ($variantDescription !== null && $variantDescription !== '' && strpos($variantDescriptions, $variantDescription) === false) {
                    $variantDescriptions .= self::VALUE_SEPARATOR . $variantDescription;
                }
            }

            return trim($variantDescriptions);
        }

        return $product->getShortDescription($domainId) ?? '';
    }

    /**
     * @return array<int, array{name: string, slug: string}>
     */
    private function extractBreadcrumb(Product $product, int $domainId, string $locale): array
    {
        return $this->breadcrumbFacade->getBreadcrumbOnDomain($product->getId(), 'front_product_detail', $domainId, $locale);
    }

    private function extractMainCategoryPath(Product $product, int $domainId, string $locale): string
    {
        $mainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);

        return $this->categoryFacade->getCategoriesNamesInPathAsString($mainCategory, $locale);
    }

    /**
     * @return \App\Model\Product\Product[]
     */
    private function getVariantsForDefaultPricingGroup(Product $mainVariant, int $domainId): array
    {
        /** @var \App\Model\Product\Product[] $variants */
        $variants = $this->productSellableVariantsProvider->getVariantsForDefaultPricingGroup($mainVariant, $domainId);

        return $variants;
    }
}
