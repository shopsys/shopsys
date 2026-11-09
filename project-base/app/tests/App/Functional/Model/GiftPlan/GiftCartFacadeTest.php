<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\GiftPlan;

use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use Doctrine\DBAL\ArrayParameterType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Tests\App\Performance\Page\QueryCountingMiddleware;
use Tests\App\Test\TransactionFunctionalTestCase;

final class GiftCartFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private GiftCartFacade $giftCartFacade;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private PricingGroupSettingFacade $pricingGroupSettingFacade;

    /**
     * @inject
     */
    private ProductVisibilityFacade $productVisibilityFacade;

    public function testOnlyOrderableVisibleGiftProductsOnStockAreSellable(): void
    {
        $giftProductOnStockId = 1;
        $giftProductOutOfStockId = 2;
        $inquiryGiftProductId = 3;
        $hiddenGiftProductId = 4;
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(Domain::FIRST_DOMAIN_ID);
        $this->setLimitedStockQuantity([$giftProductOnStockId, $hiddenGiftProductId], 10);
        $this->setLimitedStockQuantity([$giftProductOutOfStockId], 0);
        $this->em->getConnection()->executeStatement(
            'UPDATE product_visibilities SET visible = FALSE WHERE product_id = :productId AND pricing_group_id = :pricingGroupId AND domain_id = :domainId',
            [
                'productId' => $hiddenGiftProductId,
                'pricingGroupId' => $defaultPricingGroup->getId(),
                'domainId' => Domain::FIRST_DOMAIN_ID,
            ],
        );
        $this->em->clear();
        $giftProducts = $this->getProductsByIds([
            $giftProductOnStockId,
            $giftProductOutOfStockId,
            $inquiryGiftProductId,
            $hiddenGiftProductId,
        ]);

        $sellableGiftProductIds = $this->giftCartFacade->getSellableGiftProductIds(
            $giftProducts,
            $defaultPricingGroup,
            1,
            Domain::FIRST_DOMAIN_ID,
        );

        $this->assertSame([$giftProductOnStockId], $sellableGiftProductIds);
    }

    public function testSellabilityOfGiftProductsIsCheckedWithOneStockQueryAndOneVisibilityQuery(): void
    {
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());
        $giftProductIds = [1, 2, 4, 5];
        $this->setLimitedStockQuantity($giftProductIds, 10);
        $this->em->clear();
        $giftProducts = $this->getProductsByIds($giftProductIds);
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(Domain::FIRST_DOMAIN_ID);
        $queriesExecutedBeforeSellabilityCheckCount = $queryCountingMiddleware->getQueryCount();

        $this->giftCartFacade->getSellableGiftProductIds($giftProducts, $defaultPricingGroup, 1, Domain::FIRST_DOMAIN_ID);

        $this->assertCount(1, $queryCountingMiddleware->getExecutedQueriesMatching('~FROM product_stocks\b~', $queriesExecutedBeforeSellabilityCheckCount));
        $this->assertCount(1, $queryCountingMiddleware->getExecutedQueriesMatching('~FROM product_visibilities\b~', $queriesExecutedBeforeSellabilityCheckCount));
    }

    public function testSellabilityOfSingleGiftProductReusesAlreadyLoadedVisibility(): void
    {
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());
        $this->em->clear();
        $giftProduct = $this->productFacade->getById(1);
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(Domain::FIRST_DOMAIN_ID);
        $this->productVisibilityFacade->getProductVisibility($giftProduct, $defaultPricingGroup, Domain::FIRST_DOMAIN_ID);
        $queriesExecutedBeforeSellabilityCheckCount = $queryCountingMiddleware->getQueryCount();

        $this->giftCartFacade->isGiftProductSellable($giftProduct, $defaultPricingGroup, 1, Domain::FIRST_DOMAIN_ID);

        $this->assertCount(0, $queryCountingMiddleware->getExecutedQueriesMatching('~FROM product_visibilities\b~', $queriesExecutedBeforeSellabilityCheckCount));
    }

    /**
     * @param int[] $productIds
     * @return \App\Model\Product\Product[]
     */
    private function getProductsByIds(array $productIds): array
    {
        return array_map(
            fn (int $productId): Product => $this->productFacade->getById($productId),
            $productIds,
        );
    }

    /**
     * @param int[] $productIds
     */
    private function setLimitedStockQuantity(array $productIds, int $stockQuantity): void
    {
        $this->em->getConnection()->executeStatement(
            'UPDATE products SET is_allowed_negative_stock = FALSE WHERE id IN (:productIds)',
            ['productIds' => $productIds],
            ['productIds' => ArrayParameterType::INTEGER],
        );
        $this->em->getConnection()->executeStatement(
            'UPDATE product_stocks SET product_quantity = :stockQuantity WHERE product_id IN (:productIds)',
            ['stockQuantity' => $stockQuantity, 'productIds' => $productIds],
            ['productIds' => ArrayParameterType::INTEGER],
        );
    }
}
