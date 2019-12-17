<?php

namespace Shopsys\FrameworkBundle\Model\Cart;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;

class CartRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCartRepository()
    {
        return $this->em->getRepository(Cart::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     *
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
    public function deleteOldCartsForUnregisteredCustomers($daysLimit)
    {
        $nativeQuery = $this->em->createNativeQuery(
            'DELETE FROM cart_items WHERE cart_id IN (
                SELECT C.id
                FROM carts C
                WHERE C.modified_at <= :timeLimit AND customer_user_id IS NULL)',
            new ResultSetMapping()
        );

        $nativeQuery->execute([
            'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
        ]);

        $nativeQuery = $this->em->createNativeQuery(
            'DELETE FROM carts WHERE modified_at <= :timeLimit AND customer_user_id IS NULL',
            new ResultSetMapping()
        );

        $nativeQuery->execute([
            'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
        ]);
    }

    /**
     * @param int $daysLimit
     */
    public function deleteOldCartsForRegisteredCustomers($daysLimit)
    {
        $nativeQuery = $this->em->createNativeQuery(
            'DELETE FROM cart_items WHERE cart_id IN (
                SELECT C.id
                FROM carts C
                WHERE C.modified_at <= :timeLimit AND customer_user_id IS NOT NULL)',
            new ResultSetMapping()
        );

        $nativeQuery->execute([
            'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
        ]);

        $nativeQuery = $this->em->createNativeQuery(
            'DELETE FROM carts WHERE modified_at <= :timeLimit AND customer_user_id IS NOT NULL',
            new ResultSetMapping()
        );

        $nativeQuery->execute([
            'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
        ]);
    }
}
