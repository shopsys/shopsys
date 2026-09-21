<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Processing\Preloader;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Doctrine\ORM\PersistentCollection;
use ReflectionProperty;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\Preloader\OrderInputPreloaderFacade;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Tests\App\Test\FunctionalTestCase;

final class OrderInputPreloaderFacadeTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private OrderInputPreloaderFacade $orderInputPreloaderFacade;

    /**
     * @inject
     */
    private OrderInputFactory $orderInputFactory;

    public function testProductDomainsAndTranslationsAreLoadedForAllProductsInOrderInput(): void
    {
        $firstProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class);
        $orderInput = $this->orderInputFactory->create($this->domain->getCurrentDomainConfig());
        $orderInput->addProduct($firstProduct, 1);
        $orderInput->addProduct($secondProduct, 2);
        $this->assertFalse($this->isCollectionInitialized($firstProduct, 'domains'));

        $this->orderInputPreloaderFacade->preload($orderInput);

        foreach ([$firstProduct, $secondProduct] as $product) {
            $this->assertTrue($this->isCollectionInitialized($product, 'domains'));
            $this->assertTrue($this->isCollectionInitialized($product, 'translations'));
            $this->assertNotNull($product->getVatForDomain(Domain::FIRST_DOMAIN_ID)->getPercent());
        }
    }

    private function isCollectionInitialized(Product $product, string $collectionPropertyName): bool
    {
        $collection = new ReflectionProperty(BaseProduct::class, $collectionPropertyName)->getValue($product);

        return $collection instanceof PersistentCollection && $collection->isInitialized();
    }
}
