<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartItemTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private VatFacade $vatFacade;

    public function testIsSimilarItemAs()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $productData->catnum = '123';
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES, Unit::class);

        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
        $productData->manualInputPricesByPricingGroupId = [1 => Money::zero(), 2 => Money::zero()];

        $product1 = Product::create($productData);
        $productData2 = $productData;
        $productData2->catnum = '321';
        $product2 = Product::create($productData);
        $this->em->persist($product1);
        $this->em->persist($product2);
        $this->em->flush();

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cartItem2 = new CartItem($cart, $product1, 3, Money::zero());
        $cartItem3 = new CartItem($cart, $product2, 1, Money::zero());

        $this->assertTrue($cartItem1->isSimilarItemAs($cartItem2));
        $this->assertFalse($cartItem1->isSimilarItemAs($cartItem3));
    }
}
