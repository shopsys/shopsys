<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;

class CartRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCartRepository()
    {
        return $this->em->getRepository(Cart::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    public function findByCustomerUserIdentifier(CustomerUserIdentifier $customerUserIdentifier)
    {
        $criteria = [];

        if ($customerUserIdentifier->getCustomerUser() !== null) {
            $criteria['customerUser'] = $customerUserIdentifier->getCustomerUser()->getId();
        } else {
            $criteria['cartIdentifier'] = $customerUserIdentifier->getCartIdentifier();
        }

        return $this->getCartRepository()->findOneBy($criteria, ['id' => 'desc']);
    }

    /**
     * @param int $daysLimit
     */
    public function deleteOldCartsForUnregisteredCustomerUsers($daysLimit)
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

    /**
     * @param int $daysLimit
     */
    public function deleteOldCartsForRegisteredCustomerUsers($daysLimit)
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
