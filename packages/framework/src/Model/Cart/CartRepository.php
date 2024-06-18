<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;

class CartRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderStatusRepository $orderStatusRepository,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCartRepository()
    {
        return $this->em->getRepository(Order::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findByCustomerUserIdentifier(CustomerUserIdentifier $customerUserIdentifier): ?Order
    {
        $criteria = ['status' => null];

        if ($customerUserIdentifier->getCustomerUser() !== null) {
            $criteria['customerUser'] = $customerUserIdentifier->getCustomerUser()->getId();
        } else {
            $criteria['uuid'] = $customerUserIdentifier->getCartIdentifier();
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
                'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_MUTABLE,
            ],
        );

        $this->em->getConnection()->executeStatement(
            'DELETE FROM carts WHERE modified_at <= :timeLimit AND customer_user_id IS NULL',
            [
                'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_MUTABLE,
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
                'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_MUTABLE,
            ],
        );

        $this->em->getConnection()->executeStatement(
            'DELETE FROM carts WHERE modified_at <= :timeLimit AND customer_user_id IS NOT NULL',
            [
                'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_MUTABLE,
            ],
        );
    }
}
