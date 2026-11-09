<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Recalculation;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Flag\PromotionFlagFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftFlagSynchronizerFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\App\Performance\Page\QueryCountingMiddleware;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ProductFlagRecalculationQueryCountTest extends TransactionFunctionalTestCase
{
    private const int PRODUCT_WITH_PROMOTION_NUMBER = 154;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade;

    /**
     * @inject
     */
    private PromotionFlagFacade $promotionFlagFacade;

    public function testFlagRecalculationOfProductsBatchDoesNotQueryProductDomainsNorPromotionFlagsPerProduct(): void
    {
        $productIds = $this->getProductIdsOfBatchWithPromotionProduct();
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());
        $this->em->clear();
        $products = $this->productFacade->getAllRefreshedByIdsWithDomainsAndFlagsIndexedById($productIds);
        $queryCountingMiddleware->reset();

        $this->giftFlagSynchronizerFacade->recalculateGiftFlagForMainProducts($products);
        $this->promotionFlagFacade->updatePromotionFlagsForProducts($products);

        $this->assertCount(0, $queryCountingMiddleware->getExecutedQueriesMatching('~FROM product_domains\b|\bproduct_domain_flags\b~'));
        $this->assertCount(1, $queryCountingMiddleware->getExecutedQueriesMatching('~\bgift_plans\b~'));
        $this->assertCount(
            count($this->domain->getAllIds()),
            $queryCountingMiddleware->getExecutedQueriesMatching('~FROM product_visibilities\b~'),
        );
        $this->assertCount(0, $queryCountingMiddleware->getExecutedQueriesMatching('~FROM product_promotion_xy\b~'));
        $this->assertCount(1, $queryCountingMiddleware->getExecutedQueriesMatching('~FROM flags\b.*\bproduct_promotion_xy\b~s'));
    }

    /**
     * @return int[]
     */
    private function getProductIdsOfBatchWithPromotionProduct(): array
    {
        $productNumbers = [...range(1, 20), self::PRODUCT_WITH_PROMOTION_NUMBER];

        return array_map(
            fn (int $productNumber): int => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productNumber, Product::class)->getId(),
            $productNumbers,
        );
    }
}
