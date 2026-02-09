<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;

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

        return $this->getCartRepository()->findOneBy($criteria, ['id' => 'desc']);
    }

    public function deleteOldCartsForUnregisteredCustomerUsers(int $daysLimit): void
    {
        $this->em->getConnection()->executeStatement(
            'DELETE FROM cart_items WHERE cart_id IN (
                SELECT C.id
                FROM carts C
                WHERE C.modified_at <= :timeLimit AND customer_user_id IS NULL)',
            [
                'timeLimit' => $this->clock->now()->modify('-' . $daysLimit . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_IMMUTABLE,
            ],
        );

        $this->em->getConnection()->executeStatement(
            'DELETE FROM carts WHERE modified_at <= :timeLimit AND customer_user_id IS NULL',
            [
                'timeLimit' => $this->clock->now()->modify('-' . $daysLimit . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }

    public function deleteOldCartsForRegisteredCustomerUsers(int $daysLimit): void
    {
        $this->em->getConnection()->executeStatement(
            'DELETE FROM cart_items WHERE cart_id IN (
                SELECT C.id
                FROM carts C
                WHERE C.modified_at <= :timeLimit AND customer_user_id IS NOT NULL)',
            [
                'timeLimit' => $this->clock->now()->modify('-' . $daysLimit . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_IMMUTABLE,
            ],
        );

        $this->em->getConnection()->executeStatement(
            'DELETE FROM carts WHERE modified_at <= :timeLimit AND customer_user_id IS NOT NULL',
            [
                'timeLimit' => $this->clock->now()->modify('-' . $daysLimit . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }
}
