<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
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

    private Product $productWithGift;

    private Product $giftProduct;

    private Product $unrelatedProduct;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->productWithGift = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '14', Product::class);
        $this->giftProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        $this->unrelatedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
    }

    public function testRemovedGiftStaysRemovedAfterAddingUnrelatedProduct(): void
    {
        $cart = $this->createCartWithRemovedGift();

        $updatedCart = $this->addProduct($this->unrelatedProduct, 1, $cart['uuid']);

        self::assertSame([], $this->getGiftItems($updatedCart));
        self::assertSame([$this->giftProduct->getUuid()], array_column(array_column($cart['removedGifts'], 'product'), 'uuid'));
        self::assertSame($cart['removedGifts'], $updatedCart['removedGifts']);
    }

    public function testRemovedGiftStaysRemovedAfterChangingProductQuantity(): void
    {
        $cart = $this->createCartWithRemovedGift();

        $updatedCart = $this->addProduct($this->productWithGift, 3, $cart['uuid'], true);

        self::assertSame([], $this->getGiftItems($updatedCart));
        self::assertSame(3, $updatedCart['items'][0]['quantity']);
        self::assertCount(1, $updatedCart['removedGifts']);
        self::assertSame(3, $updatedCart['removedGifts'][0]['quantity']);
        self::assertSame($this->giftProduct->getUuid(), $updatedCart['removedGifts'][0]['product']['uuid']);
    }

    public function testRemovedGiftCanBeRestoredWithCurrentProductQuantity(): void
    {
        $cart = $this->createCartWithRemovedGift();
        $cart = $this->addProduct($this->productWithGift, 3, $cart['uuid'], true);

        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid());
        $updatedCart = $this->getResponseDataForGraphQlType($response, 'RestoreCartGift');
        $giftItems = $this->getGiftItems($updatedCart);

        self::assertCount(1, $giftItems);
        self::assertSame($cart['removedGifts'][0]['quantity'], array_first($giftItems)['quantity']);
        self::assertSame($this->giftProduct->getUuid(), array_first($giftItems)['product']['uuid']);
        self::assertSame([], $updatedCart['removedGifts']);
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
        $cart = $this->createCartWithRemovedGift();

        $updatedCart = $this->addProduct($this->productWithGift, $stockQuantity + 1, $cart['uuid'], true);
        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid());
        $restoredCart = $this->getResponseDataForGraphQlType($response, 'RestoreCartGift');

        self::assertSame($stockQuantity, $updatedCart['removedGifts'][0]['quantity']);
        self::assertSame($stockQuantity, array_first($this->getGiftItems($restoredCart))['quantity']);
    }

    public function testExistingGiftQuantityFollowsProductQuantity(): void
    {
        $cart = $this->addProduct($this->productWithGift, 1);

        $updatedCart = $this->addProduct($this->productWithGift, 3, $cart['uuid'], true);

        self::assertCount(1, $this->getGiftItems($updatedCart));
        self::assertSame(3, array_first($this->getGiftItems($updatedCart))['quantity']);
        self::assertSame([], $updatedCart['removedGifts']);
    }

    public function testUnrelatedProductCannotBeRestoredAsGift(): void
    {
        $cart = $this->createCartWithRemovedGift();

        $response = $this->restoreGift($cart['uuid'], $this->unrelatedProduct->getUuid());

        $this->assertResponseContainsArrayOfErrors($response);
        self::assertSame('cart-item-invalid', $this->getErrorsFromResponse($response)[0]['extensions']['userCode']);
    }

    public function testGiftCannotBeRestoredWithoutProductThatQualifiesForIt(): void
    {
        $cart = $this->createCartWithRemovedGift();
        $cart = $this->removeItem($cart['uuid'], $cart['items'][0]['uuid']);
        self::assertSame([], $cart['removedGifts']);

        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid());

        $this->assertResponseContainsArrayOfErrors($response);
        self::assertSame('cart-item-invalid', $this->getErrorsFromResponse($response)[0]['extensions']['userCode']);
    }

    public function testBuyingGiftProductNormallyDoesNotRestoreGift(): void
    {
        $cart = $this->createCartWithRemovedGift();

        $updatedCart = $this->addProduct($this->giftProduct, 1, $cart['uuid']);

        self::assertSame([], $this->getGiftItems($updatedCart));
        self::assertCount(2, $updatedCart['items']);
        self::assertSame($cart['removedGifts'], $updatedCart['removedGifts']);

        $response = $this->restoreGift($cart['uuid'], $this->giftProduct->getUuid());
        $restoredCart = $this->getResponseDataForGraphQlType($response, 'RestoreCartGift');
        self::assertCount(3, $restoredCart['items']);
        self::assertCount(1, $this->getGiftItems($restoredCart));
    }

    /**
     * @return array<string, mixed>
     */
    private function createCartWithRemovedGift(): array
    {
        $cart = $this->addProduct($this->productWithGift, 1);
        $giftItems = $this->getGiftItems($cart);
        self::assertCount(1, $giftItems, 'The demo gift plan must add a gift before it can be removed.');

        return $this->removeItem($cart['uuid'], array_first($giftItems)['uuid']);
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
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GiftCartAddToCartMutation.graphql', [
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
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GiftCartRemoveFromCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'cartItemUuid' => $cartItemUuid,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'RemoveFromCart');
    }

    /**
     * @return array<string, mixed>
     */
    private function restoreGift(string $cartUuid, string $giftProductUuid): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/RestoreCartGiftMutation.graphql', [
            'cartUuid' => $cartUuid,
            'giftProductUuid' => $giftProductUuid,
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
}
