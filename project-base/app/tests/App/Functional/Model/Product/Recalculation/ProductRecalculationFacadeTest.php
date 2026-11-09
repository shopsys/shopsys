<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Recalculation;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Redis;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDeduplicationFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityHighMessage;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityRegularMessage;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductRecalculationFacadeTest extends TransactionFunctionalTestCase
{
    private const string PROMOTED_PRODUCTS_CACHE_KEY = 'queryCache:' . CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_QUERY_KEY_PART . ':test';
    private const int REGULAR_PRIORITY_MAIN_VARIANT_NUMBER = 69;
    private const int HIGH_PRIORITY_MAIN_VARIANT_NUMBER = 82;

    /**
     * @inject snc_redis.storefront_graphql_query
     */
    private Redis $storefrontGraphqlQueryClient;

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
    private ProductRecalculationFacade $productRecalculationFacade;

    /**
     * @inject
     */
    private ProductRecalculationDeduplicationFacade $productRecalculationDeduplicationFacade;

    /**
     * @inject
     */
    private DelayedEnvelopesCollector $delayedEnvelopesCollector;

    #[Override]
    protected function tearDown(): void
    {
        $this->storefrontGraphqlQueryClient->del(self::PROMOTED_PRODUCTS_CACHE_KEY);
        $this->productRecalculationDeduplicationFacade->delete(
            [$this->getProductId(self::REGULAR_PRIORITY_MAIN_VARIANT_NUMBER)],
            ProductRecalculationPriorityEnum::REGULAR,
        );
        $this->productRecalculationDeduplicationFacade->delete(
            [$this->getProductId(self::HIGH_PRIORITY_MAIN_VARIANT_NUMBER)],
            ProductRecalculationPriorityEnum::HIGH,
        );

        parent::tearDown();
    }

    public function testHidingPromotedProductCleansPromotedProductsCache(): void
    {
        $this->storefrontGraphqlQueryClient->set(self::PROMOTED_PRODUCTS_CACHE_KEY, 'cached');

        $this->hideProductOnFirstDomain(1);

        $this->assertFalse($this->storefrontGraphqlQueryClient->get(self::PROMOTED_PRODUCTS_CACHE_KEY));
    }

    public function testHidingNotPromotedProductKeepsPromotedProductsCache(): void
    {
        $this->storefrontGraphqlQueryClient->set(self::PROMOTED_PRODUCTS_CACHE_KEY, 'cached');

        $this->hideProductOnFirstDomain(33);

        $this->assertSame('cached', $this->storefrontGraphqlQueryClient->get(self::PROMOTED_PRODUCTS_CACHE_KEY));
    }

    /**
     * @return iterable<string, array{priority: string, expectedMessageClass: class-string<\Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage>, variantNumber: int, mainVariantNumber: int}>
     */
    public static function recalculationPriorityProvider(): iterable
    {
        yield 'regular priority' => [
            'priority' => ProductRecalculationPriorityEnum::REGULAR,
            'expectedMessageClass' => ProductRecalculationPriorityRegularMessage::class,
            'variantNumber' => 53,
            'mainVariantNumber' => self::REGULAR_PRIORITY_MAIN_VARIANT_NUMBER,
        ];

        yield 'high priority' => [
            'priority' => ProductRecalculationPriorityEnum::HIGH,
            'expectedMessageClass' => ProductRecalculationPriorityHighMessage::class,
            'variantNumber' => 74,
            'mainVariantNumber' => self::HIGH_PRIORITY_MAIN_VARIANT_NUMBER,
        ];
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage> $expectedMessageClass
     */
    #[DataProvider('recalculationPriorityProvider')]
    public function testRecalculationOfVariantDispatchesItsChangedMainVariantWithSamePriority(
        string $priority,
        string $expectedMessageClass,
        int $variantNumber,
        int $mainVariantNumber,
    ): void {
        $variantId = $this->getProductId($variantNumber);
        $mainVariantId = $this->getProductId($mainVariantNumber);
        $this->em->getConnection()->executeStatement(
            'UPDATE products SET hidden = TRUE WHERE id = :mainVariantId',
            ['mainVariantId' => $mainVariantId],
        );
        $this->em->clear();
        $this->delayedEnvelopesCollector->resetEnvelopes();
        $this->productIndexBackupFacade->createSnapshot();

        $this->productRecalculationFacade->recalculate([$variantId], $priority);

        $this->assertSame([$mainVariantId], $this->popDispatchedRecalculationProductIds($expectedMessageClass));
    }

    private function hideProductOnFirstDomain(int $productReferenceId): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId, Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->domainHidden[Domain::FIRST_DOMAIN_ID] = true;
        $this->productFacade->edit($product->getId(), $productData);

        $this->handleDispatchedRecalculationMessages();
    }

    private function getProductId(int $productNumber): int
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productNumber, Product::class)->getId();
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage> $messageClass
     * @return int[]
     */
    private function popDispatchedRecalculationProductIds(string $messageClass): array
    {
        $dispatchedProductIds = [];
        $envelopes = $this->delayedEnvelopesCollector->popEnvelopes();

        foreach ($envelopes as $envelope) {
            $message = $envelope->getMessage();

            if ($message instanceof $messageClass) {
                $dispatchedProductIds[] = $message->productId;
            }
        }

        return $dispatchedProductIds;
    }
}
