<?php

namespace Tests\ShopBundle\Functional\Model\Cart;

use Shopsys\FrameworkBundle\DataFixtures\Demo\UnitDataFixture;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\ShopBundle\Model\Product\Product;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class CartItemTest extends TransactionFunctionalTestCase
{
    public function testIsSimilarItemAs()
    {
        $em = $this->getEntityManager();
        $productDataFactory = $this->getContainer()->get(ProductDataFactoryInterface::class);

        $customerIdentifier = new CustomerIdentifier('randomString');

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = 21;
        $vat = new Vat($vatData);
        $availabilityData = new AvailabilityData();
        $availabilityData->dispatchTime = 0;
        $availability = new Availability($availabilityData);
        $productData = $productDataFactory->create();
        $productData->name = [];
        $productData->price = 100;
        $productData->vat = $vat;
        $productData->availability = $availability;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);

        $product1 = Product::create($productData, new ProductCategoryDomainFactory());
        $product2 = Product::create($productData, new ProductCategoryDomainFactory());
        $em->persist($vat);
        $em->persist($availability);
        $em->persist($product1);
        $em->persist($product2);
        $em->flush();

        $cart = new Cart($customerIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, '0.0');
        $cartItem2 = new CartItem($cart, $product1, 3, '0.0');
        $cartItem3 = new CartItem($cart, $product2, 1, '0.0');

        $this->assertTrue($cartItem1->isSimilarItemAs($cartItem2));
        $this->assertFalse($cartItem1->isSimilarItemAs($cartItem3));
    }
}
