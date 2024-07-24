<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Cart\Cart;
use App\Model\Cart\Item\CartItem;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductInputPriceDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private VatFacade $vatFacade;

    /**
     * @inject
     */
    private ProductInputPriceDataFactory $productInputPriceDataFactory;

    public function testRemoveItem(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $productData->catnum = '123';
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES, Unit::class);
        $this->setVatsAndPrices($productData);
        $product1 = Product::create($productData);
        $productData2 = $productData;
        $productData2->catnum = '321';
        $product2 = Product::create($productData2);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);
        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $this->em->persist($cart);
        $this->em->persist($product1);
        $this->em->persist($product2);
        $this->em->persist($cartItem1);
        $this->em->persist($cartItem2);
        $this->em->flush();

        $cart->removeItemById($cartItem1->getId());
        $this->em->remove($cartItem1);
        $this->em->flush();

        $this->assertSame(1, $cart->getItemsCount());
    }

    public function testCleanMakesCartEmpty(): void
    {
        $product = $this->createProduct();

        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $cartItem = new CartItem($cart, $product, 1, Money::zero());
        $cart->addItem($cartItem);

        $cart->clean();

        $this->assertTrue($cart->isEmpty());
    }

    /**
     * @return \App\Model\Product\Product
     */
    private function createProduct(): Product
    {
        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = ['cs' => 'Any name'];
        $this->setVatsAndPrices($productData);

        return Product::create($productData);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVatsAndPrices(ProductData $productData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->productInputPricesByDomain[$domainId] = $this->productInputPriceDataFactory->create(
                $this->vatFacade->getDefaultVatForDomain($domainId),
                [1 => Money::zero(), 2 => Money::zero()],
            );
        }
    }
}
