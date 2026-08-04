<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\ProductReview\Exception\ProductReviewNotFoundException;

class ProductReviewRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getProductReviewRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductReview::class);
    }

    public function getById(int $productReviewId): ProductReview
    {
        $productReview = $this->getProductReviewRepository()->find($productReviewId);

        if ($productReview === null) {
            throw new ProductReviewNotFoundException($productReviewId);
        }

        return $productReview;
    }

    /**
     * Returns approved reviews of the main product and of its variants that are visible for at least one pricing group,
     * so the review visibility follows the domain, not the pricing group of a particular customer
     *
     * The newest-first order is part of the export contract — the frontend API serves the default ordering
     * directly from the exported document without re-sorting
     *
     * @return \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview[]
     */
    public function getApprovedByMainProductForExport(Product $mainProduct, int $domainId): array
    {
        $visibleProductSubquery = $this->em->createQueryBuilder()
            ->select('1')
            ->from(ProductVisibility::class, 'prv')
            ->where('prv.product = pr.product')
            ->andWhere('prv.domainId = :domainId')
            ->andWhere('prv.visible = TRUE');

        return $this->createApprovedOnDomainQueryBuilder($domainId)
            ->join('pr.product', 'p')
            ->andWhere('p = :mainProduct OR p.mainVariant = :mainProduct')
            ->andWhere(sprintf('EXISTS (%s)', $visibleProductSubquery->getDQL()))
            ->setParameter('mainProduct', $mainProduct)
            ->orderBy('pr.createdAt', 'DESC')
            ->addOrderBy('pr.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[]|null $productIds Null returns the reviews regardless of the reviewed product
     * @return \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview[]
     */
    public function getByCustomerUser(
        CustomerUser $customerUser,
        int $domainId,
        ?array $productIds,
        int $limit,
        int $offset,
    ): array {
        return $this->createCustomerUserQueryBuilder($customerUser, $domainId, $productIds)
            ->orderBy('pr.createdAt', 'DESC')
            ->addOrderBy('pr.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[]|null $productIds Null counts the reviews regardless of the reviewed product
     */
    public function getCountByCustomerUser(
        CustomerUser $customerUser,
        int $domainId,
        ?array $productIds,
    ): int {
        return (int)$this->createCustomerUserQueryBuilder($customerUser, $domainId, $productIds)
            ->select('COUNT(pr.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int[]|null $productIds
     */
    protected function createCustomerUserQueryBuilder(
        CustomerUser $customerUser,
        int $domainId,
        ?array $productIds,
    ): QueryBuilder {
        $queryBuilder = $this->getProductReviewRepository()->createQueryBuilder('pr')
            ->where('pr.customerUser = :customerUser')
            ->andWhere('pr.domainId = :domainId')
            ->setParameter('customerUser', $customerUser)
            ->setParameter('domainId', $domainId);

        if ($productIds !== null) {
            $queryBuilder
                ->andWhere('pr.product IN (:productIds)')
                ->setParameter('productIds', $productIds);
        }

        return $queryBuilder;
    }

    public function existsByCustomerUserAndProductId(
        CustomerUser $customerUser,
        int $productId,
        int $domainId,
    ): bool {
        $productReviewId = $this->getProductReviewRepository()->createQueryBuilder('pr')
            ->select('pr.id')
            ->where('pr.customerUser = :customerUser')
            ->andWhere('pr.product = :productId')
            ->andWhere('pr.domainId = :domainId')
            ->setParameter('customerUser', $customerUser)
            ->setParameter('productId', $productId)
            ->setParameter('domainId', $domainId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $productReviewId !== null;
    }

    protected function createApprovedOnDomainQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getProductReviewRepository()->createQueryBuilder('pr')
            ->where('pr.status = :approvedStatus')
            ->andWhere('pr.domainId = :domainId')
            ->setParameter('approvedStatus', ProductReviewStatusEnum::STATUS_APPROVED)
            ->setParameter('domainId', $domainId);
    }
}
