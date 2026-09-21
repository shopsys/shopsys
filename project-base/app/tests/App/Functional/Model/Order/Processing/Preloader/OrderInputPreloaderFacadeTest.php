<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Processing\Preloader;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use ReflectionProperty;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\Preloader\OrderInputPreloaderFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Tests\App\Performance\Page\QueryCountingMiddleware;
use Tests\App\Test\FunctionalTestCase;

final class OrderInputPreloaderFacadeTest extends FunctionalTestCase
{
    private const string PRODUCT_WITH_SPECIAL_PRICE_ID = '117';
    private const string PRODUCT_WITHOUT_SPECIAL_PRICE_ID = '4';

    /**
     * @inject
     */
    private OrderInputPreloaderFacade $orderInputPreloaderFacade;

    /**
     * @inject
     */
    private OrderInputFactory $orderInputFactory;

    /**
     * @inject
     */
    private EntityManagerInterface $entityManager;

    /**
     * @inject
     */
    private ProductManualInputPriceRepository $productManualInputPriceRepository;

    /**
     * @inject
     */
    private SpecialPriceFacade $specialPriceFacade;

    /**
     * @inject
     */
    private ProductVisibilityFacade $productVisibilityFacade;

    /**
     * @inject
     */
    private PricingGroupSettingFacade $pricingGroupSettingFacade;

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

    public function testPricesAndVisibilityOfOrderInputProductsAreReadWithoutFurtherQueriesAfterPreload(): void
    {
        $productWithSpecialPrice = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_WITH_SPECIAL_PRICE_ID, Product::class);
        $productWithoutSpecialPrice = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_WITHOUT_SPECIAL_PRICE_ID, Product::class);
        $domainId = $this->domain->getId();
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        $orderInput = $this->orderInputFactory->create($this->domain->getCurrentDomainConfig());
        $orderInput->addProduct($productWithSpecialPrice, 1);
        $orderInput->addProduct($productWithoutSpecialPrice, 2);

        $this->orderInputPreloaderFacade->preload($orderInput);
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->entityManager->getConnection());

        $highBasicPrice = new Price(Money::create('1000000'), Money::create('1000000'));

        foreach ([$productWithSpecialPrice, $productWithoutSpecialPrice] as $product) {
            $this->assertNotNull($this->productManualInputPriceRepository->findByProductAndPricingGroup($product, $pricingGroup));
            $this->assertTrue($this->productVisibilityFacade->getProductVisibility($product, $pricingGroup, $domainId)->isVisible());
        }

        $this->assertNotNull($this->specialPriceFacade->findRelevantSpecialPrice($productWithSpecialPrice, $domainId, $highBasicPrice));
        $this->assertNull($this->specialPriceFacade->findRelevantSpecialPrice($productWithoutSpecialPrice, $domainId, $highBasicPrice));

        $queriesOnPreloadedTables = array_filter(
            $queryCountingMiddleware->getExecutedQueries(),
            static fn (array $executedQuery): bool => preg_match('~product_manual_input_prices|price_list_product_prices|product_visibilities~', $executedQuery['sql']) === 1,
        );

        $this->assertSame([], $queriesOnPreloadedTables);
    }

    private function isCollectionInitialized(Product $product, string $collectionPropertyName): bool
    {
        $collection = new ReflectionProperty(BaseProduct::class, $collectionPropertyName)->getValue($product);

        return $collection instanceof PersistentCollection && $collection->isInitialized();
    }
}
