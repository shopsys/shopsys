<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use function sleep;

class ProductSellingDeniedOnDomainTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testSellingDeniedOnDomain(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 132, Product::class);
        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->saleExclusion[$this->domain->getId()] = true;
        $this->productFacade->edit($product->getId(), $productData);

        $this->handleDispatchedRecalculationMessages();

        // wait for elastic to reindex
        sleep(1);

        self::assertTrue($product->getSaleExclusion($this->domain->getId()));

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/ProductQuery.graphql', [
            'uuid' => $product->getUuid(),
        ]);

        /** @var array{uuid: string, name: string, isSellingDenied: bool} $product */
        $product = $this->getResponseDataForGraphQlType($response, 'product');

        self::assertTrue($product['isSellingDenied']);
    }
}
