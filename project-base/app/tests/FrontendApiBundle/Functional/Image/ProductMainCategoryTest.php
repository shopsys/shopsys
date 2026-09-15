<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Image;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Product;
use Overblog\DataLoader\DataLoader;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ProductMainCategoryTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ProductEntityFieldMapper $productEntityFieldMapper;

    public function testMainCategoryIsTheDeepestCategoryInsteadOfTheFirstAssignedCategory(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $mainCategory = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductMainCategoryQuery.graphql', [
            'productUuid' => $product->getUuid(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'product');

        $this->assertSame($mainCategory->getUuid(), $data['mainCategory']['uuid']);
        $this->assertNotSame($data['categories'][0]['uuid'], $data['mainCategory']['uuid']);
        $this->assertSame($mainCategory->getUuid(), DataLoader::await($this->productEntityFieldMapper->getMainCategoryPromise($product))?->getUuid());
    }
}
