<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Stock;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\Proxy;
use ReflectionProperty;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockRepository;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Tests\App\Test\FunctionalTestCase;

final class ProductStockRepositoryTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private ProductStockRepository $productStockRepository;

    public function testStocksAndTheirDomainsAreLoadedTogetherWithProductStocks(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $productStocks = $this->productStockRepository->getProductStocksByProduct($product);

        $this->assertNotEmpty($productStocks);

        foreach ($productStocks as $productStock) {
            $stock = $productStock->getStock();

            $this->assertTrue(!$stock instanceof Proxy || $stock->__isInitialized());
            $this->assertTrue($this->isDomainsCollectionInitialized($stock));
            $this->assertIsBool($stock->isEnabled(Domain::FIRST_DOMAIN_ID));
        }
    }

    private function isDomainsCollectionInitialized(Stock $stock): bool
    {
        $domains = new ReflectionProperty(Stock::class, 'domains')->getValue($stock);

        return $domains instanceof PersistentCollection && $domains->isInitialized();
    }
}
