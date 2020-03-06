<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class CartTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    public function testRemoveItem()
    {
        $em = $this->getEntityManager();

        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $availabilityData = new AvailabilityData();
        $availabilityData->dispatchTime = 0;
        $availability = new Availability($availabilityData);

        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $productData->availability = $availability;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $this->setVats($productData);
        $product1 = Product::create($productData);
        $product2 = Product::create($productData);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);
        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $em->persist($cart);
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

        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $cartItem = new CartItem($cart, $product, 1, Money::zero());
        $cart->addItem($cartItem);

        $cart->clean();

        $this->assertTrue($cart->isEmpty());
    }

    /**
     * @return \App\Model\Product\Product
     */
    private function createProduct()
    {
        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = ['cs' => 'Any name'];
        $this->setVats($productData);
        $product = Product::create($productData);

        return $product;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
