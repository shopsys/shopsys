<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\EntityExtension;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use Doctrine\DBAL\ArrayParameterType;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Tests\App\Performance\Page\QueryCountingMiddleware;
use Tests\App\Test\TransactionFunctionalTestCase;

final class EntityManagerDecoratorTest extends TransactionFunctionalTestCase
{
    private const int CHANGED_WEIGHT = 12345;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    public function testRefreshLoadedEntitiesByClassNameRefreshesProductsInSingleQuery(): void
    {
        $productIds = $this->getProductIds();
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());
        $this->em->clear();
        $products = $this->productFacade->getAllByIds($productIds);
        $this->changeProductsWeight($productIds);
        $queryCountingMiddleware->reset();

        $this->em->refreshLoadedEntitiesByClassName(Product::class);

        $this->assertCount(1, $queryCountingMiddleware->getExecutedQueries());

        foreach ($products as $product) {
            $this->assertSame(self::CHANGED_WEIGHT, $product->getWeight());
        }
    }

    public function testRefreshLoadedEntitiesByClassNameRefreshesEntitiesWithCompositeIdentifier(): void
    {
        $productId = array_first($this->getProductIds());
        $this->em->clear();
        $productVisibilities = $this->em->getRepository(ProductVisibility::class)->findBy(['product' => $productId]);
        $expectedVisibleValues = array_map(
            static fn (ProductVisibility $productVisibility): bool => !$productVisibility->isVisible(),
            $productVisibilities,
        );
        $this->em->getConnection()->executeStatement(
            'UPDATE product_visibilities SET visible = NOT visible WHERE product_id = :productId',
            ['productId' => $productId],
        );

        $this->em->refreshLoadedEntitiesByClassName(ProductVisibility::class);

        $this->assertNotSame([], $productVisibilities);
        $this->assertSame(
            $expectedVisibleValues,
            array_map(static fn (ProductVisibility $productVisibility): bool => $productVisibility->isVisible(), $productVisibilities),
        );
    }

    public function testRefreshLoadedEntitiesByClassNameKeepsEntityOfDeletedRowManagedLikeRefresh(): void
    {
        $productId = array_first($this->getProductIds());
        $this->em->clear();
        $productVisibilities = $this->em->getRepository(ProductVisibility::class)->findBy(['product' => $productId]);
        $deletedProductVisibility = array_first($productVisibilities);
        $visibleBeforeDeletion = $deletedProductVisibility->isVisible();
        $deletedProductVisibilityIdentifier = $this->em->getUnitOfWork()->getEntityIdentifier($deletedProductVisibility);
        $this->em->getConnection()->executeStatement(
            'DELETE FROM product_visibilities WHERE product_id = :productId AND pricing_group_id = :pricingGroupId AND domain_id = :domainId',
            [
                'productId' => $deletedProductVisibilityIdentifier['product'],
                'pricingGroupId' => $deletedProductVisibilityIdentifier['pricingGroup'],
                'domainId' => $deletedProductVisibilityIdentifier['domainId'],
            ],
        );

        $this->em->refreshLoadedEntitiesByClassName(ProductVisibility::class);

        $this->assertTrue($this->em->contains($deletedProductVisibility));
        $this->assertSame($visibleBeforeDeletion, $deletedProductVisibility->isVisible());
    }

    public function testRefreshLoadedEntitiesByClassNameSkipsUninitializedProxies(): void
    {
        $productId = array_first($this->getProductIds());
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());
        $this->em->clear();
        $productProxy = $this->em->getReference(Product::class, $productId);
        $this->changeProductsWeight([$productId]);
        $queryCountingMiddleware->reset();

        $this->em->refreshLoadedEntitiesByClassName(Product::class);

        $this->assertSame(0, $queryCountingMiddleware->getQueryCount());
        $this->assertSame(self::CHANGED_WEIGHT, $productProxy->getWeight());
    }

    /**
     * @return int[]
     */
    private function getProductIds(): array
    {
        return array_map(
            fn (int $productNumber): int => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productNumber, Product::class)->getId(),
            [1, 2, 3],
        );
    }

    /**
     * @param int[] $productIds
     */
    private function changeProductsWeight(array $productIds): void
    {
        $this->em->getConnection()->executeStatement(
            'UPDATE products SET weight = :weight WHERE id IN (:productIds)',
            ['weight' => self::CHANGED_WEIGHT, 'productIds' => $productIds],
            ['productIds' => ArrayParameterType::INTEGER],
        );
    }
}
