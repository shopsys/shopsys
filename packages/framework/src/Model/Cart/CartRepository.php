<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use SortDirection;

class CartRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function getCartRepository(): EntityRepository
    {
        return $this->em->getRepository(Cart::class);
    }

    public function findByCustomerUserIdentifier(CustomerUserIdentifier $customerUserIdentifier): ?Cart
    {
        $criteria = [];

        if ($customerUserIdentifier->getCustomerUser() !== null) {
            $criteria['customerUser'] = $customerUserIdentifier->getCustomerUser()->getId();
        } else {
            $criteria['cartIdentifier'] = $customerUserIdentifier->getCartIdentifier();
        }

        $cart = $this->getCartRepository()->findOneBy($criteria, ['id' => SortDirection::Descending]);

        if ($cart !== null) {
            $this->loadItemsWithAdditionalServices($cart);
        }

        return $cart;
    }

    protected function loadItemsWithAdditionalServices(Cart $cart): void
    {
        $this->em->createQueryBuilder()
            ->select('ci', 'a')
            ->from(CartItem::class, 'ci')
            ->leftJoin('ci.additionalServices', 'a')
            ->where('ci.cart = :cart')->setParameter('cart', $cart)
            ->getQuery()->getResult();
    }

    public function deleteOldCartsForUnregisteredCustomerUsers(int $daysLimit): void
    {
        $this->em->getConnection()->executeStatement(
            'DELETE FROM cart_items WHERE cart_id IN (
                SELECT C.id
                FROM carts C
                WHERE C.last_activity_at <= :dateLimit AND customer_user_id IS NULL)',
            [
                'dateLimit' => $this->clock->now()->modify('-' . $daysLimit . ' days'),
            ],
            [
                'dateLimit' => Types::DATE_IMMUTABLE,
            ],
        );

        $this->em->getConnection()->executeStatement(
            'DELETE FROM carts WHERE last_activity_at <= :dateLimit AND customer_user_id IS NULL',
            [
                'dateLimit' => $this->clock->now()->modify('-' . $daysLimit . ' days'),
            ],
            [
                'dateLimit' => Types::DATE_IMMUTABLE,
            ],
        );
    }

    public function deleteOldCartsForRegisteredCustomerUsers(int $daysLimit): void
    {
        $this->em->getConnection()->executeStatement(
            'DELETE FROM cart_items WHERE cart_id IN (
                SELECT C.id
                FROM carts C
                WHERE C.last_activity_at <= :dateLimit AND customer_user_id IS NOT NULL)',
            [
                'dateLimit' => $this->clock->now()->modify('-' . $daysLimit . ' days'),
            ],
            [
                'dateLimit' => Types::DATE_IMMUTABLE,
            ],
        );

        $this->em->getConnection()->executeStatement(
            'DELETE FROM carts WHERE last_activity_at <= :dateLimit AND customer_user_id IS NOT NULL',
            [
                'dateLimit' => $this->clock->now()->modify('-' . $daysLimit . ' days'),
            ],
            [
                'dateLimit' => Types::DATE_IMMUTABLE,
            ],
        );
    }
}
