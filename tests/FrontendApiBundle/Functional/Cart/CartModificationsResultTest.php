<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartModificationsResultTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private Product $testingProduct;

    /**
     * @var \App\Model\Product\ProductFacade
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @var \App\Model\Product\ProductDataFactory
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
    }

    public function testModificationTriggeredInAddToCartMutation(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);

        $this->hideTestingProduct();

        $addToCartMutation = 'mutation {
            AddToCart(input: {
                cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                productUuid: "' . $secondProduct->getUuid() . '",
                quantity: ' . $productQuantity . '
            }) {
                modifications {
                    noLongerListableCartItems {
                        uuid
                    }
                }
            }
        }';
        $response = $this->getResponseContentForQuery($addToCartMutation);
        $modifications = $response['data']['AddToCart']['modifications'];

        self::assertNotEmpty($modifications['noLongerListableCartItems']);
    }

    public function testModificationTriggeredInRemoveFromCartMutation(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);
        $addToCartMutation = 'mutation {
            AddToCart(input: {
                cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                productUuid: "' . $secondProduct->getUuid() . '",
                quantity: ' . $productQuantity . '
            }) {
                items {
                    uuid
                }
            }
        }';
        $response = $this->getResponseContentForQuery($addToCartMutation);
        $cartItemUuid = $response['data']['AddToCart']['items'][1]['uuid'];

        // product has to be refreshed to prevent Doctrine from trying to flush not-persisted entity Vat
        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->hideTestingProduct();

        $removeFromCartMutation = 'mutation {
            RemoveFromCart(input: {
                cartUuid: "' . $newlyCreatedCart['uuid'] . '"
                cartItemUuid: "' . $cartItemUuid . '"
            }) {
                modifications {
                    noLongerListableCartItems {
                        uuid
                    }
                }
            }
        }';
        $response = $this->getResponseContentForQuery($removeFromCartMutation);
        $modifications = $response['data']['RemoveFromCart']['modifications'];

        self::assertNotEmpty($modifications['noLongerListableCartItems']);
    }

    public function testNoLongerListableCartItemIsReported(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $this->hideTestingProduct();

        $getCartQuery = '{
            cart(uuid: "' . $newlyCreatedCart['uuid'] . '") {
                modifications {
                    noLongerListableCartItems {
                        uuid
                        product {
                            uuid
                        }
                    }
                    noLongerAvailableCartItemsDueToQuantity {
                        uuid
                    }
                    cartItemsWithModifiedPrice {
                        uuid
                    }
                    cartItemsWithChangedQuantity {
                        uuid
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];

        self::assertNotEmpty($modifications['noLongerListableCartItems']);
        self::assertEquals($this->testingProduct->getUuid(), $modifications['noLongerListableCartItems'][0]['product']['uuid']);

        self::assertEmpty($modifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($modifications['cartItemsWithModifiedPrice']);
        self::assertEmpty($modifications['cartItemsWithChangedQuantity']);
    }

    public function testCartItemWithModifiedPriceIsReported(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $this->modifyPriceOfTestingProduct();

        $getCartQuery = '{
            cart(uuid: "' . $newlyCreatedCart['uuid'] . '") {
                modifications {
                    cartItemsWithModifiedPrice {
                        uuid
                        product {
                            uuid
                        }
                    }
                    noLongerAvailableCartItemsDueToQuantity {
                        uuid
                    }
                    noLongerListableCartItems {
                        uuid
                    }
                    cartItemsWithChangedQuantity {
                        uuid
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];

        self::assertNotEmpty($modifications['cartItemsWithModifiedPrice']);
        self::assertEquals($this->testingProduct->getUuid(), $modifications['cartItemsWithModifiedPrice'][0]['product']['uuid']);

        self::assertEmpty($modifications['noLongerListableCartItems']);
        self::assertEmpty($modifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($modifications['cartItemsWithChangedQuantity']);
    }

    public function testCartItemWithChangedQuantityIsReported(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $this->setOneItemLeftOnStockForTestingProduct();

        $getCartQuery = '{
            cart(uuid: "' . $newlyCreatedCart['uuid'] . '") {
                modifications {
                    cartItemsWithChangedQuantity {
                        uuid
                        product {
                            uuid
                        }
                    }
                    noLongerAvailableCartItemsDueToQuantity {
                        uuid
                    }
                    noLongerListableCartItems {
                        uuid
                    }
                    cartItemsWithModifiedPrice {
                        uuid
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];

        self::assertNotEmpty($modifications['cartItemsWithChangedQuantity']);
        self::assertEquals($this->testingProduct->getUuid(), $modifications['cartItemsWithChangedQuantity'][0]['product']['uuid']);

        self::assertEmpty($modifications['noLongerListableCartItems']);
        self::assertEmpty($modifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEmpty($modifications['cartItemsWithModifiedPrice']);
    }

    public function testNoLongerAvailableCartItemDueToQuantityIsReported(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $this->setNoItemLeftOnStockForTestingProduct();

        $getCartQuery = '{
            cart(uuid: "' . $newlyCreatedCart['uuid'] . '") {
                modifications {
                    noLongerAvailableCartItemsDueToQuantity {
                        uuid
                        product {
                            uuid
                        }
                    }
                    noLongerListableCartItems {
                        uuid
                    }
                    cartItemsWithModifiedPrice {
                        uuid
                    }
                    cartItemsWithChangedQuantity {
                        uuid
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $modifications = $response['data']['cart']['modifications'];

        self::assertNotEmpty($modifications['noLongerAvailableCartItemsDueToQuantity']);
        self::assertEquals($this->testingProduct->getUuid(), $modifications['noLongerAvailableCartItemsDueToQuantity'][0]['product']['uuid']);

        self::assertEmpty($modifications['noLongerListableCartItems']);
        self::assertEmpty($modifications['cartItemsWithModifiedPrice']);
        self::assertEmpty($modifications['cartItemsWithChangedQuantity']);
    }

    /**
     * @param int $productQuantity
     * @return array
     */
    private function addTestingProductToNewCart(int $productQuantity): array
    {
        $mutation = 'mutation {
            AddToCart(input: {
                productUuid: "' . $this->testingProduct->getUuid() . '",
                quantity: ' . $productQuantity . '
            }) {
                uuid
            }
        }';

        $response = $this->getResponseContentForQuery($mutation);
        return $response['data']['AddToCart'];
    }

    private function hideTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);
        $productData->sellingDenied = true;

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }

    private function modifyPriceOfTestingProduct(): void
    {
        $pricingGroupFacade = $this->getContainer()->get(PricingGroupFacade::class);

        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($pricingGroupFacade->getAll() as $pricingGroup) {
            $productData->manualInputPricesByPricingGroupId[$pricingGroup->getId()] = Money::create(1);
        }

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }

    private function setOneItemLeftOnStockForTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($productData->stockProductData as $stockProductData) {
            $stockProductData->productQuantity = 0;
        }

        $productData->stockProductData[1]->productQuantity = 1;

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }

    private function setNoItemLeftOnStockForTestingProduct(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->testingProduct);

        foreach ($productData->stockProductData as $stockProductData) {
            $stockProductData->productQuantity = 0;
        }

        $this->productFacade->edit($this->testingProduct->getId(), $productData);
        $this->dispatchFakeKernelResponseEventToTriggerImmediateRecalculations();
    }
}
