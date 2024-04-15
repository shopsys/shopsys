<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Cart\CartFacade;
use App\Model\Cart\Item\CartItem;
use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;

class CartDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const CART_UUID = '1007c9a3-f570-484a-b84e-4a4f49bb35c0';

    /**
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private CartFacade $cartFacade,
        protected CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->createAnonymousCart();
        $this->createCartForCustomerUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
            CustomerUserDataFixture::class,
        ];
    }

    /**
     * @param int $id
     * @param string $uuid
     */
    private function updateCartItemUuid(int $id, string $uuid): void
    {
        $this->em
            ->createQuery(
                sprintf(
                    'UPDATE %s ci SET ci.uuid = \'%s\' WHERE ci.id = %d',
                    CartItem::class,
                    $uuid,
                    $id,
                ),
            )
            ->execute();
    }

    private function createAnonymousCart(): void
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->getOnlyWithCartIdentifier(self::CART_UUID);
        $cart = $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $result = $this->cartFacade->addProductToExistingCart($product, 2, $cart);
        $this->updateCartItemUuid($result->getCartItem()->getId(), '5096bd50-45e1-40a6-bbe8-6192592feb56');

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        $result = $this->cartFacade->addProductToExistingCart($product, 2, $cart);
        $this->updateCartItemUuid($result->getCartItem()->getId(), 'f0d0cb7c-f873-4107-8187-f733d292b02f');
    }

    private function createCartForCustomerUser(): void
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '6'));
        $cart = $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $result = $this->cartFacade->addProductToExistingCart($product, 1, $cart);
        $this->updateCartItemUuid($result->getCartItem()->getId(), '37b71f81-3b14-4ce9-ae7d-9c8465c13464');
    }
}
