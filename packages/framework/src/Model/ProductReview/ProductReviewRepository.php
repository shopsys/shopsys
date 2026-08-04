<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    protected function createApprovedOnDomainQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getProductReviewRepository()->createQueryBuilder('pr')
            ->where('pr.status = :approvedStatus')
            ->andWhere('pr.domainId = :domainId')
            ->setParameter('approvedStatus', ProductReviewStatusEnum::STATUS_APPROVED)
            ->setParameter('domainId', $domainId);
    }
}
