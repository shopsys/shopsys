<?php

declare(strict_types=1);

namespace App\Model\Wishlist;

use App\Model\Customer\User\CustomerUser;
use App\Model\Wishlist\Item\WishlistItem;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class WishlistRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(Wishlist::class);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function findByCustomerUser(CustomerUser $customerUser): ?Wishlist
    {
        return $this->getRepository()->findOneBy(['customerUser' => $customerUser]);
    }

    /**
     * @param string $uuid
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function findByUuid(string $uuid): ?Wishlist
    {
        return $this->getRepository()->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @param int $id
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function findById(int $id): ?Wishlist
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param \App\Model\Wishlist\Wishlist $wishlist
     * @return int[]
     */
    public function getProductIdsByWishlist(Wishlist $wishlist): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('p.id')
            ->from(WishlistItem::class, 'wi')
            ->join('wi.product', 'p')
            ->where('wi.wishlist = :wishlist')
            ->orderBy('wi.createdAt', 'DESC')
            ->setParameter('wishlist', $wishlist)
            ->getQuery()
            ->getScalarResult();

        return array_map(fn ($row) => $row['id'], $result);
    }

    public function removeOldWishlists(): void
    {
        $removeDate = new DateTime('-31day');

        $this->em->createQueryBuilder()
            ->delete(Wishlist::class, 'w')
            ->where('w.customerUser IS NULL')
            ->andWhere('w.updatedAt < :removeDate')
            ->setParameter('removeDate', $removeDate)
            ->getQuery()
            ->execute();
    }
}
