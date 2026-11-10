<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\GiftPlanDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanDataFactory;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CartGiftTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductAvailabilityFacade $productAvailabilityFacade;

    /**
     * @inject
     */
    private GiftPlanFacade $giftPlanFacade;

    /**
     * @inject
     */
    private GiftPlanDataFactory $giftPlanDataFactory;

    private Product $productWithGift;

    private Product $giftProduct;

    private Product $unrelatedProduct;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $giftPlan = $this->getReferenceForDomain(GiftPlanDataFixture::GIFT_PLAN_VALID, Domain::FIRST_DOMAIN_ID, GiftPlan::class);

        /** @var \App\Model\Product\Product $productWithGift */
        $productWithGift = array_first($giftPlan->getMainProducts());
        $this->productWithGift = $productWithGift;

        /** @var \App\Model\Product\Product $giftProduct */
        $giftProduct = $giftPlan->getGiftProduct();
        $this->giftProduct = $giftProduct;

        $this->unrelatedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
    }

    public function testOfferedGiftQuantityRespectsAvailableStock(): void
    {
        $giftData = $this->productDataFactory->createFromProduct($this->giftProduct);
        $giftData->isAllowedNegativeStock = false;
        $this->productFacade->edit($this->giftProduct->getId(), $giftData);
        $productData = $this->productDataFactory->createFromProduct($this->productWithGift);
        $productData->isAllowedNegativeStock = true;
        $this->productFacade->edit($this->productWithGift->getId(), $productData);
        $stockQuantity = $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($this->giftProduct, Domain::FIRST_DOMAIN_ID);
        self::assertNotNull($stockQuantity);
        self::assertGreaterThan(0, $stockQuantity);

        $cart = $this->addProduct($this->productWithGift, $stockQuantity + 1);

        self::assertSame($stockQuantity, array_first($this->getGiftItems($cart))['quantity']);
    }

    public function testExistingGiftQuantityFollowsProductQuantity(): void
    {
        $cart = $this->addProduct($this->productWithGift, 1);

        $updatedCart = $this->addProduct($this->productWithGift, 3, $cart['uuid'], true);

        self::assertCount(1, $this->getGiftItems($updatedCart));
        self::assertSame(3, array_first($this->getGiftItems($updatedCart))['quantity']);
    }

    public function testSharedGiftIsSeparateItemRelatedToEachProduct(): void
    {
        $this->addGiftPlan($this->giftProduct, [$this->unrelatedProduct]);

        $cart = $this->addProduct($this->productWithGift, 5);
        $cart = $this->addProduct($this->unrelatedProduct, 3, $cart['uuid']);

        $gifts = $this->getGiftItemsByMainProductUuid($cart);
        self::assertCount(2, $gifts);
        self::assertSame(5, $gifts[$this->productWithGift->getUuid()]['quantity']);
        self::assertSame(3, $gifts[$this->unrelatedProduct->getUuid()]['quantity']);
    }

    public function testDifferentGiftsForOneProductAreSeparateItems(): void
    {
        $this->addGiftPlan($this->unrelatedProduct, [$this->productWithGift]);

        $cart = $this->addProduct($this->productWithGift, 2);

        $gifts = $this->getGiftItems($cart);
        self::assertCount(2, $gifts);
        self::assertEqualsCanonicalizing(
            [$this->giftProduct->getUuid(), $this->unrelatedProduct->getUuid()],
            array_values(array_map(static fn (array $item) => $item['product']['uuid'], $gifts)),
        );
    }

    /**
     * @param \App\Model\Product\Product[] $mainProducts
     */
    private function addGiftPlan(Product $gift, array $mainProducts): void
    {
        $data = $this->giftPlanDataFactory->create();
        $data->name = 'Independent gift regression';
        $data->domainId = Domain::FIRST_DOMAIN_ID;
        $data->giftProduct = $gift;
        $data->mainProducts = $mainProducts;
        $this->giftPlanFacade->create($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function addProduct(
        Product $product,
        int $quantity,
        ?string $cartUuid = null,
        bool $isAbsoluteQuantity = false,
    ): array {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product->getUuid(),
            'quantity' => $quantity,
            'isAbsoluteQuantity' => $isAbsoluteQuantity,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart'];
    }

    /**
     * @param array<string, mixed> $cart
     * @return array<int, array<string, mixed>>
     */
    private function getGiftItems(array $cart): array
    {
        return array_filter($cart['items'], static fn (array $item) => $item['type'] === 'productGift');
    }

    /**
     * @param array<string, mixed> $cart
     * @return array<string, array<string, mixed>>
     */
    private function getGiftItemsByMainProductUuid(array $cart): array
    {
        $giftItemsByUuid = array_column($this->getGiftItems($cart), null, 'uuid');
        $giftItemsByMainProductUuid = [];

        foreach ($cart['items'] as $item) {
            foreach ($item['relatedItems'] as $relatedItem) {
                if (isset($giftItemsByUuid[$relatedItem['uuid']])) {
                    $giftItemsByMainProductUuid[$item['product']['uuid']] = $giftItemsByUuid[$relatedItem['uuid']];
                }
            }
        }

        return $giftItemsByMainProductUuid;
    }
}
