<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\GiftPlan;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Doctrine\ORM\Events;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftFlagSynchronizerFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanRepository;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Tests\App\Performance\Page\QueryCountingMiddleware;
use Tests\App\Test\EntityManagerClearCounter;
use Tests\App\Test\ProductQueryPattern;
use Tests\App\Test\TransactionFunctionalTestCase;

final class GiftFlagSynchronizerFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private GiftPlanRepository $giftPlanRepository;

    /**
     * @inject
     */
    private FlagFacade $flagFacade;

    /**
     * @inject
     */
    private ProductRecalculationDispatcher $productRecalculationDispatcher;

    /**
     * @inject
     */
    private PricingGroupSettingFacade $pricingGroupSettingFacade;

    /**
     * @inject
     */
    private GiftCartFacade $giftCartFacade;

    public function testMainProductGetsGiftFlagWhenGiftProductIsSellable(): void
    {
        $this->removeGiftFlagFromProduct($this->getMainProductId());
        $this->assertFalse($this->hasProductGiftFlag($this->getMainProductId()));

        $this->recalculateGiftFlagOfMainProduct();

        $this->assertTrue($this->hasProductGiftFlag($this->getMainProductId()));
    }

    public function testMainProductLosesGiftFlagWhenGiftProductIsSellingDenied(): void
    {
        $this->assertTrue($this->hasProductGiftFlag($this->getMainProductId()));

        $giftProduct = $this->productFacade->getById($this->getGiftProductId());
        $giftProductData = $this->productDataFactory->createFromProduct($giftProduct);
        $giftProductData->sellingDenied = true;
        $this->productFacade->edit($giftProduct->getId(), $giftProductData);
        $this->handleDispatchedRecalculationMessages();

        $this->recalculateGiftFlagOfMainProduct();

        $this->assertFalse($this->hasProductGiftFlag($this->getMainProductId()));
    }

    public function testRefreshForGiftProductIdsDispatchesMainProductsForGiftFlagsRecalculation(): void
    {
        $productRecalculationDispatcherMock = $this->createMock(ProductRecalculationDispatcher::class);
        $productRecalculationDispatcherMock->expects($this->once())
            ->method('dispatchProductIds')
            ->with([$this->getMainProductId()], ProductRecalculationPriorityEnum::REGULAR, [ProductExportScopeConfig::SCOPE_GIFT_FLAGS]);

        $this->createGiftFlagSynchronizerFacadeWithProductRecalculationDispatcher($productRecalculationDispatcherMock)
            ->refreshForGiftProductIds([$this->getGiftProductId()]);
    }

    public function testRefreshAllGiftPlansLoadsAndRecalculatesMainProductsInBatches(): void
    {
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());
        $mainProductIds = $this->giftPlanRepository->getAllMainProductIds();
        $hasGiftFlagIndexedByMainProductId = $this->getHasGiftFlagIndexedByProductId($mainProductIds);
        $this->assertGreaterThan(1, count($mainProductIds));
        $this->assertContains(true, $hasGiftFlagIndexedByMainProductId);

        foreach ($mainProductIds as $mainProductId) {
            $this->removeGiftFlagFromProduct($mainProductId);
        }

        $this->assertNotContains(true, $this->getHasGiftFlagIndexedByProductId($mainProductIds));

        $queryCountingMiddleware->reset();

        $this->createGiftFlagSynchronizerFacadeWithSingleProductBatches()->refreshAllGiftPlans();
        $this->em->clear();

        $mainProductsBatchLoadingQueries = $queryCountingMiddleware->getExecutedQueriesMatching(ProductQueryPattern::PRODUCTS_WITH_DOMAINS_AND_FLAGS_LOAD);
        $this->assertCount(count($mainProductIds), $mainProductsBatchLoadingQueries);
        $this->assertSame($hasGiftFlagIndexedByMainProductId, $this->getHasGiftFlagIndexedByProductId($mainProductIds));
    }

    public function testRefreshAllGiftPlansClearsEntityManagerAfterEachBatch(): void
    {
        $mainProductIds = $this->giftPlanRepository->getAllMainProductIds();
        $entityManagerClearCounter = new EntityManagerClearCounter();
        $this->em->getEventManager()->addEventListener(Events::onClear, $entityManagerClearCounter);

        $this->createGiftFlagSynchronizerFacadeWithSingleProductBatches()->refreshAllGiftPlans();

        $this->em->getEventManager()->removeEventListener(Events::onClear, $entityManagerClearCounter);
        $this->assertGreaterThan(1, count($mainProductIds));
        $this->assertSame(count($mainProductIds), $entityManagerClearCounter->getClearsCount());
    }

    private function createGiftFlagSynchronizerFacadeWithProductRecalculationDispatcher(
        ProductRecalculationDispatcher $productRecalculationDispatcher,
    ): GiftFlagSynchronizerFacade {
        return new GiftFlagSynchronizerFacade(
            ...$this->getGiftFlagSynchronizerFacadeConstructorArguments($productRecalculationDispatcher),
        );
    }

    private function createGiftFlagSynchronizerFacadeWithSingleProductBatches(): GiftFlagSynchronizerFacade
    {
        return new class(...$this->getGiftFlagSynchronizerFacadeConstructorArguments($this->productRecalculationDispatcher)) extends GiftFlagSynchronizerFacade {
            protected const int REFRESH_ALL_BATCH_SIZE = 1;
        };
    }

    /**
     * @return array{\Doctrine\ORM\EntityManagerInterface, \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanRepository, \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain, \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade, \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartFacade, \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache, \Shopsys\FrameworkBundle\Model\Product\ProductFacade}
     */
    private function getGiftFlagSynchronizerFacadeConstructorArguments(
        ProductRecalculationDispatcher $productRecalculationDispatcher,
    ): array {
        return [
            $this->em,
            $this->giftPlanRepository,
            $this->flagFacade,
            $this->domain,
            $productRecalculationDispatcher,
            $this->pricingGroupSettingFacade,
            $this->giftCartFacade,
            $this->inMemoryCache,
            $this->productFacade,
        ];
    }

    private function getMainProductId(): int
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '14', Product::class)->getId();
    }

    private function getGiftProductId(): int
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class)->getId();
    }

    private function removeGiftFlagFromProduct(int $productId): void
    {
        $product = $this->productFacade->getById($productId);
        $productFlagsWithoutGiftFlag = array_values(array_filter(
            $product->getFlags(Domain::FIRST_DOMAIN_ID),
            static fn (Flag $flag): bool => $flag->getUuid() !== GiftFlagSynchronizerFacade::FLAG_GIFT_UUID,
        ));
        $product->setFlags([Domain::FIRST_DOMAIN_ID => $productFlagsWithoutGiftFlag]);
        $this->em->flush();
        $this->em->clear();
    }

    private function recalculateGiftFlagOfMainProduct(): void
    {
        $this->em->clear();
        $this->giftFlagSynchronizerFacade->recalculateGiftFlagForMainProducts(
            $this->productFacade->getAllRefreshedByIdsWithDomainsAndFlagsIndexedById([$this->getMainProductId()]),
        );
        $this->em->clear();
    }

    /**
     * @param int[] $productIds
     * @return array<int, bool>
     */
    private function getHasGiftFlagIndexedByProductId(array $productIds): array
    {
        $hasGiftFlagIndexedByProductId = [];

        foreach ($productIds as $productId) {
            $hasGiftFlagIndexedByProductId[$productId] = $this->hasProductGiftFlag($productId);
        }

        return $hasGiftFlagIndexedByProductId;
    }

    private function hasProductGiftFlag(int $productId): bool
    {
        $productFlags = $this->productFacade->getById($productId)->getFlags(Domain::FIRST_DOMAIN_ID);

        foreach ($productFlags as $flag) {
            if ($flag->getUuid() === GiftFlagSynchronizerFacade::FLAG_GIFT_UUID) {
                return true;
            }
        }

        return false;
    }
}
