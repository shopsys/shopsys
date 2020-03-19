<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\ProductTypeDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartItemTest extends TransactionFunctionalTestCase
{
    /**
     * @var \App\Model\Product\ProductDataFactory
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    public function testIsSimilarItemAs()
    {
        $em = $this->getEntityManager();

        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $availabilityData = new AvailabilityData();
        $availabilityData->dispatchTime = 0;
        $availability = new Availability($availabilityData);
        /** @var \App\Model\Product\Type\ProductType $productType */
        $productType = $this->getReference(ProductTypeDataFixture::TYPE_COMMON);

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $productData->availability = $availability;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $productData->productType = [
            Domain::FIRST_DOMAIN_ID => $productType,
            Domain::SECOND_DOMAIN_ID => $productType,
        ];

        $productVatsIndexedByDomainId = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;

        $product1 = Product::create($productData);
        $product2 = Product::create($productData);
        $em->persist($availability);
        $em->persist($product1);
        $em->persist($product2);
        $em->flush();

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cartItem2 = new CartItem($cart, $product1, 3, Money::zero());
        $cartItem3 = new CartItem($cart, $product2, 1, Money::zero());

        $this->assertTrue($cartItem1->isSimilarItemAs($cartItem2));
        $this->assertFalse($cartItem1->isSimilarItemAs($cartItem3));
    }
}
