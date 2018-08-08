<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;

class CartItemRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getCartItemRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(CartItem::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getAllByCustomerIdentifier(CustomerIdentifier $customerIdentifier): array
    {
        $criteria = [];
        if ($customerIdentifier->getUser() !== null) {
            $criteria['user'] = $customerIdentifier->getUser()->getId();
        } else {
            $criteria['cartIdentifier'] = $customerIdentifier->getCartIdentifier();
        }

        return $this->getCartItemRepository()->findBy($criteria, ['id' => 'desc']);
    }
    
    public function deleteOldCartsForUnregisteredCustomers(int $daysLimit): void
    {
        $nativeQuery = $this->em->createNativeQuery(
            'DELETE FROM cart_items WHERE cart_identifier NOT IN (
                SELECT CI.cart_identifier
                FROM cart_items CI
                WHERE CI.added_at > :timeLimit
            ) AND user_id IS NULL',
            new ResultSetMapping()
        );

        $nativeQuery->execute([
            'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
        ]);
    }
    
    public function deleteOldCartsForRegisteredCustomers(int $daysLimit): void
    {
        $nativeQuery = $this->em->createNativeQuery(
            'DELETE FROM cart_items WHERE user_id NOT IN (
                SELECT CI.user_id
                FROM cart_items CI
                WHERE CI.added_at > :timeLimit
            ) AND user_id IS NOT NULL',
            new ResultSetMapping()
        );

        $nativeQuery->execute([
            'timeLimit' => new DateTime('-' . $daysLimit . ' days'),
        ]);
    }
}
