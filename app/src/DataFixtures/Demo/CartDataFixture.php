<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Cart\CartFacade;
use App\Model\Customer\User\CustomerUserIdentifierFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class CartDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const CART_UUID = '1007c9a3-f570-484a-b84e-4a4f49bb35c0';

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\Model\Customer\User\CustomerUserIdentifierFactory
     */
    protected CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    /**
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     */
    public function __construct(
        CartFacade $cartFacade,
        CustomerUserIdentifierFactory $customerUserIdentifierFactory
    ) {
        $this->cartFacade = $cartFacade;
        $this->customerUserIdentifierFactory = $customerUserIdentifierFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCartIdentifier(self::CART_UUID);
        $cart = $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $this->cartFacade->addProductToExistingCart($product, 4, $cart);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72');
        $this->cartFacade->addProductToExistingCart($product, 2, $cart);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
