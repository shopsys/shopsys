<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Flag;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Doctrine\ORM\Events;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\PromotionFlagFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyRepository;
use Tests\App\Performance\Page\QueryCountingMiddleware;
use Tests\App\Test\EntityManagerClearCounter;
use Tests\App\Test\ProductQueryPattern;
use Tests\App\Test\TransactionFunctionalTestCase;

final class PromotionFlagFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private FlagFacade $flagFacade;

    /**
     * @inject
     */
    private FlagDataFactory $flagDataFactory;

    /**
     * @inject
     */
    private ProductPromotionXyRepository $productPromotionXyRepository;

    /**
     * @inject
     */
    private ProductPromotionXyFactory $productPromotionXyFactory;

    /**
     * @inject
     */
    private ProductPromotionXyDataFactory $productPromotionXyDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testUpdatePromotionFlagsForAllLoadsAndUpdatesProductsInBatches(): void
    {
        $queryCountingMiddleware = QueryCountingMiddleware::createInjectedInto($this->em->getConnection());
        $productWithoutPromotion = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->addPromotionToProduct($productWithoutPromotion->getId());
        $productIdsWithPromotion = $this->productPromotionXyRepository->getAllProductIdsWithPromotionXy();
        $this->assertGreaterThan(1, count($productIdsWithPromotion));

        foreach ($productIdsWithPromotion as $productId) {
            $this->removePromotionFlagsFromProduct($productId);
        }

        $queryCountingMiddleware->reset();

        $this->createPromotionFlagFacadeWithSingleProductBatches()->updatePromotionFlagsForAll();
        $this->em->clear();

        $productsBatchLoadingQueries = $queryCountingMiddleware->getExecutedQueriesMatching(ProductQueryPattern::PRODUCTS_WITH_DOMAINS_AND_FLAGS_LOAD);
        $this->assertCount(count($productIdsWithPromotion), $productsBatchLoadingQueries);

        foreach ($productIdsWithPromotion as $productId) {
            $this->assertTrue($this->hasProductPromotionFlag($productId));
        }
    }

    public function testUpdatePromotionFlagsForAllClearsEntityManagerAfterEachBatch(): void
    {
        $productWithoutPromotion = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->addPromotionToProduct($productWithoutPromotion->getId());
        $productIdsWithPromotion = $this->productPromotionXyRepository->getAllProductIdsWithPromotionXy();
        $entityManagerClearCounter = new EntityManagerClearCounter();
        $this->em->getEventManager()->addEventListener(Events::onClear, $entityManagerClearCounter);

        $this->createPromotionFlagFacadeWithSingleProductBatches()->updatePromotionFlagsForAll();

        $this->em->getEventManager()->removeEventListener(Events::onClear, $entityManagerClearCounter);
        $this->assertGreaterThan(1, count($productIdsWithPromotion));
        $this->assertSame(count($productIdsWithPromotion), $entityManagerClearCounter->getClearsCount());
    }

    private function createPromotionFlagFacadeWithSingleProductBatches(): PromotionFlagFacade
    {
        return new class($this->flagFacade, $this->em, $this->flagDataFactory, $this->domain, $this->productPromotionXyRepository, $this->productPromotionXyFactory, $this->productPromotionXyDataFactory, $this->productFacade) extends PromotionFlagFacade {
            protected const int UPDATE_ALL_BATCH_SIZE = 1;
        };
    }

    private function addPromotionToProduct(int $productId): void
    {
        $product = $this->productFacade->getById($productId);
        $productData = $this->productDataFactory->createFromProduct($product);
        $domainIds = $this->domain->getAllIds();

        foreach ($domainIds as $domainId) {
            $promotionXyData = $this->productPromotionXyDataFactory->create();
            $promotionXyData->buyQuantity = 3;
            $promotionXyData->freeQuantity = 1;
            $productData->promotionXyData[$domainId] = $promotionXyData;
        }

        $this->productFacade->edit($productId, $productData);
        $this->em->clear();
    }

    private function removePromotionFlagsFromProduct(int $productId): void
    {
        $product = $this->productFacade->getById($productId);
        $productFlagsWithoutPromotionFlags = array_values(array_filter(
            $product->getFlags(Domain::FIRST_DOMAIN_ID),
            static fn (Flag $flag): bool => !$flag->hasPromotionXy(),
        ));
        $product->setFlags([Domain::FIRST_DOMAIN_ID => $productFlagsWithoutPromotionFlags]);
        $this->em->flush();
        $this->em->clear();

        $this->assertFalse($this->hasProductPromotionFlag($productId));
    }

    private function hasProductPromotionFlag(int $productId): bool
    {
        $productFlags = $this->productFacade->getById($productId)->getFlags(Domain::FIRST_DOMAIN_ID);

        foreach ($productFlags as $flag) {
            if ($flag->hasPromotionXy()) {
                return true;
            }
        }

        return false;
    }
}
