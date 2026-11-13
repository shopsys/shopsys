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

    public function testRemovedGiftStaysRemovedAfterAddingUnrelatedProduct(): void
    {
        $cart = $this->createCartWithRemovedGift();

        $updatedCart = $this->addProduct($this->unrelatedProduct, 1, $cart['uuid']);

        self::assertSame([], $this->getGiftItems($updatedCart));
        self::assertSame($cart['removedProductGifts'], $updatedCart['removedProductGifts']);
    }

    public function testRemovedGiftStaysRemovedAfterChangingProductQuantity(): void
    {
        $cart = $this->createCartWithRemovedGift();

        $updatedCart = $this->addProduct($this->productWithGift, 3, $cart['uuid'], true);

        self::assertSame([], $this->getGiftItems($updatedCart));
        self::assertSame(3, $updatedCart['items'][0]['quantity']);
        self::assertCount(1, $updatedCart['removedProductGifts']);
        self::assertSame($this->giftProduct->getUuid(), $updatedCart['removedProductGifts'][0]['giftProduct']['uuid']);
    }

    public function testRemovedGiftCanBeRestoredWithCurrentProductQuantity(): void
    {
        $cart = $this->createCartWithRemovedGift();
        $cart = $this->addProduct($this->productWithGift, 3, $cart['uuid'], true);

        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid(), $this->productWithGift->getUuid());
        $updatedCart = $this->getResponseDataForGraphQlType($response, 'RestoreCartGift');
        $giftItems = $this->getGiftItems($updatedCart);

        self::assertCount(1, $giftItems);
        self::assertSame(3, array_first($giftItems)['quantity']);
        self::assertSame($this->giftProduct->getUuid(), array_first($giftItems)['product']['uuid']);
        self::assertSame([], $updatedCart['removedProductGifts']);
    }

    public function testRestoredGiftQuantityRespectsAvailableStock(): void
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
        $cart = $this->createCartWithRemovedGift();

        $updatedCart = $this->addProduct($this->productWithGift, $stockQuantity + 1, $cart['uuid'], true);
        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid(), $this->productWithGift->getUuid());
        $restoredCart = $this->getResponseDataForGraphQlType($response, 'RestoreCartGift');

        self::assertCount(1, $updatedCart['removedProductGifts']);
        self::assertSame($stockQuantity, array_first($this->getGiftItems($restoredCart))['quantity']);
    }

    public function testExistingGiftQuantityFollowsProductQuantity(): void
    {
        $cart = $this->addProduct($this->productWithGift, 1);

        $updatedCart = $this->addProduct($this->productWithGift, 3, $cart['uuid'], true);

        self::assertCount(1, $this->getGiftItems($updatedCart));
        self::assertSame(3, array_first($this->getGiftItems($updatedCart))['quantity']);
        self::assertSame([], $updatedCart['removedProductGifts']);
    }

    public function testUnrelatedProductCannotBeRestoredAsGift(): void
    {
        $cart = $this->createCartWithRemovedGift();

        $response = $this->restoreGift($cart['uuid'], $this->unrelatedProduct->getUuid(), $this->productWithGift->getUuid());

        $this->assertUserError($response, 'cart-item-invalid');
    }

    public function testGiftCannotBeRestoredAfterItsMainProductIsRemovedFromCart(): void
    {
        $cart = $this->createCartWithRemovedGift();
        $productWithGiftItemUuid = $cart['items'][0]['uuid'];
        $cart = $this->removeItem($cart['uuid'], $productWithGiftItemUuid);
        self::assertSame([], $cart['removedProductGifts']);

        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid(), $this->productWithGift->getUuid());

        $this->assertUserError($response, 'cart-item-invalid');
    }

    public function testRemovedGiftIsAddedAgainWhenQualifyingProductReturnsToCart(): void
    {
        $cart = $this->createCartWithRemovedGift();
        $cart = $this->removeItem($cart['uuid'], $cart['items'][0]['uuid']);

        $updatedCart = $this->addProduct($this->productWithGift, 1, $cart['uuid']);

        self::assertCount(1, $this->getGiftItems($updatedCart));
        self::assertSame($this->giftProduct->getUuid(), array_first($this->getGiftItems($updatedCart))['product']['uuid']);
        self::assertSame([], $updatedCart['removedProductGifts']);
    }

    public function testBuyingGiftProductNormallyDoesNotRestoreGift(): void
    {
        $cart = $this->createCartWithRemovedGift();

        $updatedCart = $this->addProduct($this->giftProduct, 1, $cart['uuid']);

        self::assertSame([], $this->getGiftItems($updatedCart));
        self::assertCount(2, $updatedCart['items']);
        self::assertSame($cart['removedProductGifts'], $updatedCart['removedProductGifts']);
    }

    public function testGiftIsRestoredAsSeparateItemWhenGiftProductIsAlsoBoughtNormally(): void
    {
        $cart = $this->createCartWithRemovedGift();
        $cart = $this->addProduct($this->giftProduct, 1, $cart['uuid']);

        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid(), $this->productWithGift->getUuid());
        $restoredCart = $this->getResponseDataForGraphQlType($response, 'RestoreCartGift');

        self::assertCount(3, $restoredCart['items']);
        self::assertCount(1, $this->getGiftItems($restoredCart));
        self::assertSame([], $restoredCart['removedProductGifts']);
    }

    public function testSharedGiftCanBeRemovedAndRestoredForEachProductIndependently(): void
    {
        $this->addGiftPlan($this->giftProduct, [$this->unrelatedProduct]);
        $cart = $this->addProduct($this->productWithGift, 5);
        $cart = $this->addProduct($this->unrelatedProduct, 3, $cart['uuid']);
        $gifts = $this->getGiftItemsByMainProductUuid($cart);
        self::assertCount(2, $gifts);
        self::assertSame(5, $gifts[$this->productWithGift->getUuid()]['quantity']);
        self::assertSame(3, $gifts[$this->unrelatedProduct->getUuid()]['quantity']);

        $cart = $this->removeItem($cart['uuid'], $gifts[$this->productWithGift->getUuid()]['uuid']);
        $cart = $this->addProduct($this->productWithGift, 2, $cart['uuid'], true);

        self::assertCount(1, $this->getGiftItems($cart));
        self::assertSame($this->unrelatedProduct->getUuid(), array_key_first($this->getGiftItemsByMainProductUuid($cart)));
        self::assertSame(3, array_first($this->getGiftItems($cart))['quantity']);
        self::assertSame($this->productWithGift->getUuid(), $cart['removedProductGifts'][0]['mainProduct']['uuid']);

        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid(), $this->productWithGift->getUuid());
        $cart = $this->getResponseDataForGraphQlType($response, 'RestoreCartGift');
        $gifts = $this->getGiftItemsByMainProductUuid($cart);
        self::assertSame(2, $gifts[$this->productWithGift->getUuid()]['quantity']);
        self::assertSame(3, $gifts[$this->unrelatedProduct->getUuid()]['quantity']);
        self::assertSame([], $cart['removedProductGifts']);
    }

    public function testOnlyRequestedRemovedGiftPairIsRestored(): void
    {
        $this->addGiftPlan($this->giftProduct, [$this->unrelatedProduct]);
        $cart = $this->addProduct($this->unrelatedProduct, 3);
        $cart = $this->addProduct($this->productWithGift, 2, $cart['uuid']);

        foreach ($this->getGiftItems($cart) as $gift) {
            $cart = $this->removeItem($cart['uuid'], $gift['uuid']);
        }

        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid(), $this->unrelatedProduct->getUuid());
        $cart = $this->getResponseDataForGraphQlType($response, 'RestoreCartGift');

        self::assertCount(1, $this->getGiftItems($cart));
        self::assertSame($this->unrelatedProduct->getUuid(), array_key_first($this->getGiftItemsByMainProductUuid($cart)));
        self::assertSame($this->productWithGift->getUuid(), $cart['removedProductGifts'][0]['mainProduct']['uuid']);
    }

    public function testDifferentGiftsForOneProductRemainIndependent(): void
    {
        $this->addGiftPlan($this->unrelatedProduct, [$this->productWithGift]);
        $cart = $this->addProduct($this->productWithGift, 2);
        self::assertCount(2, $this->getGiftItems($cart));
        $originalGift = array_first(array_filter($this->getGiftItems($cart), fn (array $item) => $item['product']['uuid'] === $this->giftProduct->getUuid()));

        $cart = $this->removeItem($cart['uuid'], $originalGift['uuid']);

        self::assertCount(1, $this->getGiftItems($cart));
        self::assertSame($this->unrelatedProduct->getUuid(), array_first($this->getGiftItems($cart))['product']['uuid']);
        self::assertSame($this->giftProduct->getUuid(), $cart['removedProductGifts'][0]['giftProduct']['uuid']);
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
    private function createCartWithRemovedGift(): array
    {
        $cart = $this->addProduct($this->productWithGift, 1);
        $giftItems = $this->getGiftItems($cart);
        self::assertCount(1, $giftItems, 'The demo gift plan must add a gift before it can be removed.');

        $cart = $this->removeItem($cart['uuid'], array_first($giftItems)['uuid']);
        self::assertSame([$this->giftProduct->getUuid()], array_column(array_column($cart['removedProductGifts'], 'giftProduct'), 'uuid'));

        return $cart;
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
     * @return array<string, mixed>
     */
    private function removeItem(string $cartUuid, string $cartItemUuid): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveFromCart.graphql', [
            'cartUuid' => $cartUuid,
            'cartItemUuid' => $cartItemUuid,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'RemoveFromCart');
    }

    /**
     * @return array<string, mixed>
     */
    private function restoreGift(string $cartUuid, string $giftProductUuid, string $mainProductUuid): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/RestoreCartGift.graphql', [
            'cartUuid' => $cartUuid,
            'giftProductUuid' => $giftProductUuid,
            'mainProductUuid' => $mainProductUuid,
        ]);
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
