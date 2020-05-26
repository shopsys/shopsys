<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Cart;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Shopsys\ShopBundle\DataFixtures\Demo\UnitDataFixture;
use Shopsys\ShopBundle\Model\Product\Product;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class CartTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    public function testRemoveItem()
    {
        $em = $this->getEntityManager();

        $customerIdentifier = new CustomerIdentifier('randomString');

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = '21';
        $vat = new Vat($vatData);
        $availabilityData = new AvailabilityData();
        $availabilityData->dispatchTime = 0;
        $availability = new Availability($availabilityData);

        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $productData->vat = $vat;
        $productData->availability = $availability;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $product1 = Product::create($productData);
        $product2 = Product::create($productData);

        $cart = new Cart($customerIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);
        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $em->persist($cart);
        $em->persist($vat);
        $em->persist($availability);
        $em->persist($product1);
        $em->persist($product2);
        $em->persist($cartItem1);
        $em->persist($cartItem2);
        $em->flush();

        $cart->removeItemById($cartItem1->getId());
        $em->remove($cartItem1);
        $em->flush();

        $this->assertSame(1, $cart->getItemsCount());
    }

    public function testCleanMakesCartEmpty()
    {
        $product = $this->createProduct();

        $customerIdentifier = new CustomerIdentifier('randomString');

        $cart = new Cart($customerIdentifier->getCartIdentifier());

        $cartItem = new CartItem($cart, $product, 1, Money::zero());
        $cart->addItem($cartItem);

        $cart->clean();

        $this->assertTrue($cart->isEmpty());
    }

    /**
     * @return \Shopsys\ShopBundle\Model\Product\Product
     */
    private function createProduct()
    {
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = '21';
        $vat = new Vat($vatData);

        /** @var \Shopsys\ShopBundle\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = ['cs' => 'Any name'];
        $productData->vat = $vat;
        $product = Product::create($productData);

        return $product;
    }
}
