<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Elasticsearch;

use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductExportRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductExportRepository $repository;

    public function testProductDataHaveExpectedStructure(): void
    {
        $data = $this->repository->getProductsData($this->domain->getId(), $this->domain->getLocale(), 0, 10);
        $this->assertCount(10, $data);

        $structure = array_keys(array_first($data));
        sort($structure);

        $expectedStructure = $this->getExpectedStructureForRepository();

        sort($expectedStructure);

        $this->assertSame($expectedStructure, $structure);
    }

    public function testMainVariantExportsFlagsFromSellableVariants(): void
    {
        $variant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '75', Product::class);
        $mainVariant = $variant->getMainVariant();

        $data = $this->repository->getProductsDataForIds(
            $this->domain->getId(),
            $this->domain->getLocale(),
            [$mainVariant->getId()],
            [ProductExportFieldProvider::FLAGS],
        );

        $expectedFlags = [
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId(),
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ, Flag::class)->getId(),
        ];
        sort($expectedFlags);

        $this->assertSame($expectedFlags, $data[$mainVariant->getId()][ProductExportFieldProvider::FLAGS]);
    }

    /**
     * @return string[]
     */
    private function getExpectedStructureForRepository(): array
    {
        return [
            'id',
            'catnum',
            'partno',
            'ean',
            'name',
            'description',
            'short_description',
            'brand',
            'brand_name',
            'brand_slug',
            'flags',
            'categories',
            'main_category_id',
            'main_category_path',
            'in_stock',
            'prices',
            'special_prices',
            'parameters',
            'ordering_priority',
            'selling_denied',
            'personal_pickup_only',
            'availability',
            'availability_status',
            'is_main_variant',
            'is_variant',
            'slug',
            'visibility',
            'product_type',
            'priority_by_product_type',
            'uuid',
            'unit',
            'stock_quantity',
            'is_allowed_negative_stock',
            'variants',
            'main_variant_id',
            'seo_h1',
            'seo_title',
            'seo_meta_description',
            'accessories',
            'name_prefix',
            'name_suffix',
            'store_availabilities_information',
            'usps',
            ...$this->getSearchingFields(),
            'available_stores_count',
            'related_products',
            'breadcrumb',
            'product_videos',
            'hreflang_links',
            'selling_from',
            'vat_percent',
            'promotion',
            'is_promoted',
            'top_product_position',
            'zbozi_category',
        ];
    }

    /**
     * @return string[]
     */
    private function getSearchingFields(): array
    {
        return [
            'searching_names',
            'searching_descriptions',
            'searching_catnums',
            'searching_eans',
            'searching_partnos',
            'searching_short_descriptions',
            'searching_seo_titles',
            'searching_seo_h1s',
            'searching_seo_meta_descriptions',
        ];
    }
}
